<?php

namespace Tests\Feature;

use App\Models\Membership;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class UsaPaymentsPaymentMethodTest extends TestCase
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

    public function test_member_can_update_usa_payments_card_on_file(): void
    {
        Http::fake([
            'usapayments.transactiongateway.com/*' => Http::response('response=1&response_code=100&transactionid=555'),
        ]);

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
            'currency' => 'USD',
            'active' => true,
        ]);

        Membership::create([
            'membership_number' => 'HERO-PM-1',
            'plan_id' => $plan->id,
            'account_user_id' => $user->id,
            'status' => 'active',
            'billing_provider' => 'usa_payments',
            'billing_subscription_id' => '12362239268',
            'auto_renew' => false,
        ]);

        $this->actingAs($user)
            ->post('/my/membership/payment-method/usa-payments', [
                'payment_token' => 'tok_update_123',
            ])
            ->assertRedirect(route('customer.membership.billing'));

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'transact.php')
                && $request['recurring'] === 'update_subscription'
                && $request['subscription_id'] === '12362239268'
                && $request['payment_token'] === 'tok_update_123';
        });

        $this->assertDatabaseHas('memberships', [
            'membership_number' => 'HERO-PM-1',
            'auto_renew' => 1,
            'billing_auto_collect' => 1,
        ]);
    }
}
