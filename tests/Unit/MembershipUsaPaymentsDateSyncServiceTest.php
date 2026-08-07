<?php

namespace Tests\Unit;

use App\Models\Membership;
use App\Models\Plan;
use App\Models\User;
use App\Services\MembershipUsaPaymentsDateSyncService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MembershipUsaPaymentsDateSyncServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['usa_payments.security_key' => 'test-security-key']);
    }

    public function test_prefers_first_payment_for_coverage_start(): void
    {
        Http::fake([
            '*' => Http::sequence()
                ->push('<?xml version="1.0"?><nm_response><transaction><action><success>1</success><date>20251113120000</date></action></transaction></nm_response>')
                ->push('<?xml version="1.0"?><nm_response><transaction><action><success>1</success><date>20251114120000</date></action></transaction></nm_response>'),
        ]);

        $dates = app(MembershipUsaPaymentsDateSyncService::class)->resolveCoverageDates([
            'subscription_id' => '11142957081',
            'plan_name' => 'Individual Plan Monthly Payment',
            'day_frequency' => 30,
            'next_charge_date' => Carbon::parse('2026-08-10'),
        ]);

        $this->assertSame('2025-11-13', $dates['coverage_starts_on']?->toDateString());
        $this->assertSame('2026-08-10', $dates['coverage_ends_on']?->toDateString());
    }

    public function test_resolves_annual_dates_from_next_charge_date(): void
    {
        Http::fake([
            '*' => Http::sequence()
                ->push('<?xml version="1.0"?><nm_response></nm_response>')
                ->push('<?xml version="1.0"?><nm_response></nm_response>'),
        ]);

        $service = app(MembershipUsaPaymentsDateSyncService::class);

        $dates = $service->resolveCoverageDates([
            'subscription_id' => '11540197555',
            'plan_name' => 'Individual Plan Annual VIP',
            'day_frequency' => 365,
            'next_charge_date' => Carbon::parse('2027-01-02'),
        ]);

        $this->assertSame('2026-01-02', $dates['coverage_starts_on']?->toDateString());
        $this->assertSame('2027-01-02', $dates['coverage_ends_on']?->toDateString());
        $this->assertSame('active', $dates['status']);
    }

    public function test_sync_updates_membership_matched_by_subscription_id(): void
    {
        $plan = Plan::create([
            'code' => 'HR-02CY',
            'name' => 'Individual Plan Annual VIP',
            'category' => 'retail',
            'retail_subgroup' => 'annual_individual',
            'sort_order' => 1,
            'billing_interval' => 'yearly',
            'price' => 199.99,
            'currency' => 'USD',
            'active' => true,
        ]);
        $user = User::factory()->create(['email' => 'annual@example.com']);
        $membership = Membership::create([
            'membership_number' => 'HERO-TEST-1',
            'plan_id' => $plan->id,
            'account_user_id' => $user->id,
            'billing_subscription_id' => '11540197555',
            'billing_provider' => 'usa_payments',
            'coverage_starts_on' => now()->toDateString(),
            'coverage_ends_on' => now()->addYear()->toDateString(),
            'status' => 'active',
        ]);

        Http::fake([
            '*' => Http::response('<?xml version="1.0"?><nm_response></nm_response>'),
        ]);

        $result = app(MembershipUsaPaymentsDateSyncService::class)->syncSubscription([
            'subscription_id' => '11540197555',
            'email' => 'annual@example.com',
            'gateway_plan_id' => 'HR-02CY',
            'plan_name' => 'Individual Plan Annual VIP',
            'day_frequency' => 365,
            'next_charge_date' => Carbon::parse('2027-01-02'),
        ], apply: true);

        $membership->refresh();

        $this->assertTrue($result['updated']);
        $this->assertSame('2026-01-02', $membership->coverage_starts_on?->toDateString());
        $this->assertSame('2027-01-02', $membership->coverage_ends_on?->toDateString());
        $this->assertSame('2027-01-02', $membership->billing_next_billing_at?->toDateString());
    }
}
