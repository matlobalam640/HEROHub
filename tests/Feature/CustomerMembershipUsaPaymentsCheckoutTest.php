<?php

namespace Tests\Feature;

use App\Models\Membership;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CustomerMembershipUsaPaymentsCheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'usa_payments.security_key' => 'test-security-key',
            'usa_payments.tokenization_key' => 'test-tokenization-key',
        ]);
    }

    public function test_update_plan_redirects_to_usa_payments_review_when_enabled(): void
    {
        $user = User::factory()->create();
        $user->assignRole('customer');

        $current = Plan::create([
            'code' => 'HR-02',
            'name' => 'Local annual',
            'category' => 'retail',
            'retail_subgroup' => 'annual_individual',
            'sort_order' => 1,
            'billing_interval' => 'yearly',
            'price' => 199.99,
            'price_monthly' => 20.83,
            'currency' => 'USD',
            'active' => true,
        ]);

        $target = Plan::create([
            'code' => 'HR-02C',
            'name' => 'VIP annual',
            'category' => 'retail',
            'retail_subgroup' => 'annual_individual',
            'sort_order' => 2,
            'billing_interval' => 'yearly',
            'price' => 398.98,
            'price_monthly' => 41.56,
            'currency' => 'USD',
            'active' => true,
        ]);

        Membership::create([
            'membership_number' => 'HERO-USA-1',
            'plan_id' => $current->id,
            'account_user_id' => $user->id,
            'status' => 'active',
            'auto_renew' => true,
        ]);

        $response = $this->actingAs($user)->post('/my/membership/plan', [
            'plan_id' => $target->id,
            'interval' => 'yearly',
        ]);

        $response->assertRedirect();
        $this->assertStringContainsString('/my/membership/plan/usa-payments-checkout/', $response->headers->get('Location'));
    }

    public function test_renew_checkout_page_loads_for_mapped_plan(): void
    {
        $user = User::factory()->create();
        $user->assignRole('customer');

        $plan = Plan::create([
            'code' => 'HR-02',
            'name' => 'Local annual',
            'category' => 'retail',
            'retail_subgroup' => 'annual_individual',
            'sort_order' => 1,
            'billing_interval' => 'yearly',
            'price' => 199.99,
            'price_monthly' => 20.83,
            'currency' => 'USD',
            'active' => true,
        ]);

        Membership::create([
            'membership_number' => 'HERO-USA-2',
            'plan_id' => $plan->id,
            'account_user_id' => $user->id,
            'status' => 'active',
            'auto_renew' => false,
            'coverage_ends_on' => now()->addDays(10),
        ]);

        $this->actingAs($user)
            ->get('/my/membership/renew')
            ->assertOk()
            ->assertSee('Membership renewal')
            ->assertSee('Local annual');
    }

    public function test_submit_checkout_updates_membership_on_approved_gateway_response(): void
    {
        Http::fake([
            'usapayments.transactiongateway.com/*' => Http::response('response=1&response_code=100&transactionid=999888777&subscription_id=SUB-USA-1'),
        ]);

        $user = User::factory()->create(['email' => 'renew@example.com']);
        $user->assignRole('customer');

        $plan = Plan::create([
            'code' => 'HR-02',
            'name' => 'Local annual',
            'category' => 'retail',
            'retail_subgroup' => 'annual_individual',
            'sort_order' => 1,
            'billing_interval' => 'yearly',
            'price' => 199.99,
            'price_monthly' => 20.83,
            'currency' => 'USD',
            'active' => true,
        ]);

        $membership = Membership::create([
            'membership_number' => 'HERO-USA-3',
            'plan_id' => $plan->id,
            'account_user_id' => $user->id,
            'status' => 'expired',
            'auto_renew' => false,
            'coverage_ends_on' => now()->subDay(),
        ]);

        $response = $this->actingAs($user)->post('/my/membership/usa-payments-checkout', [
            'payment_token' => 'tok_test_123',
            'first_name' => 'Jane',
            'last_name' => 'Member',
            'email' => 'renew@example.com',
            'phone' => '555-0100',
            'country' => 'USA',
            'state' => 'FL',
            'street' => '1 Main St',
            'city' => 'Miami',
            'zip_code' => '33101',
        ]);

        $response->assertRedirect(route('customer.membership'));

        $membership->refresh();
        $this->assertSame('active', $membership->status);
        $this->assertSame('usa_payments', $membership->billing_provider);
        $this->assertSame('SUB-USA-1', $membership->billing_subscription_id);
        $this->assertTrue($membership->auto_renew);
    }
}
