<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\WalkInEnrollmentController;
use App\Models\Membership;
use App\Models\Plan;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AdminWalkInEnrollmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        config([
            'usa_payments.security_key' => 'test-security-key',
            'usa_payments.tokenization_key' => 'test-tokenization-key',
        ]);
    }

    private function createRetailPlan(): Plan
    {
        return Plan::create([
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
    }

    public function test_admin_can_open_walk_in_enrollment_form(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->createRetailPlan();
        Plan::create([
            'code' => 'SMB-01',
            'name' => 'Small business team',
            'category' => 'business',
            'sort_order' => 1,
            'billing_interval' => 'monthly',
            'price_monthly' => 199.00,
            'currency' => 'USD',
            'active' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.enrollment.index'))
            ->assertOk()
            ->assertSee('Enroll walk-in member', false)
            ->assertSee('Small business team', false)
            ->assertSee('Local annual', false);
    }

    public function test_manual_walk_in_enrollment_accepts_small_business_plan(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $plan = Plan::create([
            'code' => 'SMB-02',
            'name' => 'Small business annual',
            'category' => 'business',
            'sort_order' => 1,
            'billing_interval' => 'yearly',
            'price' => 2400.00,
            'currency' => 'USD',
            'active' => true,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.enrollment.store'), [
                'plan_id' => $plan->id,
                'interval' => 'yearly',
                'payment_method' => 'manual',
                'first_name' => 'Biz',
                'last_name' => 'WalkIn',
                'email' => 'biz.walkin@example.com',
            ])
            ->assertRedirect(route('admin.enrollment.index'))
            ->assertSessionHas('status');

        $membership = Membership::query()->whereHas('accountUser', fn ($q) => $q->where('email', 'biz.walkin@example.com'))->first();
        $this->assertNotNull($membership);
        $this->assertSame($plan->id, $membership->plan_id);
        $this->assertSame('active', $membership->status);
    }

    public function test_manual_walk_in_enrollment_creates_active_membership(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $plan = $this->createRetailPlan();

        $this->actingAs($admin)
            ->post(route('admin.enrollment.store'), [
                'plan_id' => $plan->id,
                'interval' => 'yearly',
                'payment_method' => 'manual',
                'first_name' => 'Walk',
                'last_name' => 'In',
                'email' => 'walkin@example.com',
                'phone' => '+1 555 010 2000',
            ])
            ->assertRedirect(route('admin.enrollment.index'))
            ->assertSessionHas('status');

        $membership = Membership::query()->whereHas('accountUser', fn ($q) => $q->where('email', 'walkin@example.com'))->first();
        $this->assertNotNull($membership);
        $this->assertSame('active', $membership->status);
        $this->assertSame('manual', $membership->billing_provider);
        $this->assertMatchesRegularExpression('/^HERO-WLK-\d{4}-\d{6}$/', $membership->membership_number);
    }

    public function test_usa_payments_walk_in_flow_activates_membership_after_checkout(): void
    {
        Http::fake([
            'usapayments.transactiongateway.com/*' => Http::response('response=1&response_code=100&transactionid=999888777&subscription_id=SUB-WLK-1'),
        ]);

        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $plan = $this->createRetailPlan();

        $response = $this->actingAs($admin)->post(route('admin.enrollment.store'), [
            'plan_id' => $plan->id,
            'interval' => 'yearly',
            'payment_method' => 'usa_payments',
            'first_name' => 'Card',
            'last_name' => 'Payer',
            'email' => 'card.walkin@example.com',
            'phone' => '+1 555 010 3000',
        ]);

        $response->assertRedirect();
        $location = $response->headers->get('Location');
        $this->assertNotNull($location);
        $this->assertStringContainsString('/admin/enrollment/checkout/', $location);

        $token = basename(parse_url($location, PHP_URL_PATH) ?: '');
        $this->assertNotSame('', $token);

        $membership = Membership::query()->whereHas('accountUser', fn ($q) => $q->where('email', 'card.walkin@example.com'))->first();
        $this->assertNotNull($membership);
        $this->assertSame('inactive', $membership->status);

        $this->actingAs($admin)
            ->post(route('admin.enrollment.checkout.submit'), [
                'token' => $token,
                'payment_token' => 'tok_test_123',
                'first_name' => 'Card',
                'last_name' => 'Payer',
                'email' => 'card.walkin@example.com',
                'phone' => '555-010-3000',
                'country' => 'USA',
                'state' => 'FL',
                'street' => '1 Main St',
                'city' => 'Miami',
                'zip_code' => '33101',
            ])
            ->assertRedirect(route('admin.enrollment.index'))
            ->assertSessionHas('status');

        $membership->refresh();
        $this->assertSame('active', $membership->status);
        $this->assertSame('usa_payments', $membership->billing_provider);
        $this->assertSame('SUB-WLK-1', $membership->billing_subscription_id);
        $this->assertNull(Cache::get(WalkInEnrollmentController::CHECKOUT_CACHE_PREFIX.$token));
    }
}
