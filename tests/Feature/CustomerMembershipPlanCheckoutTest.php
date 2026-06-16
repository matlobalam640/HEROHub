<?php

namespace Tests\Feature;

use App\Models\Membership;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerMembershipPlanCheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['stripe.secret' => '']);
    }

    public function test_subscribe_from_catalog_requires_membership(): void
    {
        $user = User::factory()->create();
        $user->assignRole('customer');

        $plan = Plan::create([
            'code' => 'HR-SUB-1',
            'name' => 'Sub test plan',
            'category' => 'retail',
            'sort_order' => 1,
            'billing_interval' => 'monthly',
            'price' => 99,
            'currency' => 'USD',
            'active' => true,
        ]);

        $this->actingAs($user)->get('/my/membership/plan/subscribe/'.$plan->id.'/monthly')
            ->assertRedirect(route('portal.plans.retail'));
    }

    public function test_update_plan_without_stripe_updates_database(): void
    {
        $user = User::factory()->create();
        $user->assignRole('customer');

        $a = Plan::create([
            'code' => 'HR-CK-A',
            'name' => 'Plan A',
            'category' => 'retail',
            'sort_order' => 1,
            'billing_interval' => 'yearly',
            'price' => 100,
            'currency' => 'USD',
            'active' => true,
        ]);

        $b = Plan::create([
            'code' => 'HR-CK-B',
            'name' => 'Plan B',
            'category' => 'retail',
            'sort_order' => 2,
            'billing_interval' => 'yearly',
            'price' => 200,
            'currency' => 'USD',
            'active' => true,
        ]);

        $membership = Membership::create([
            'membership_number' => 'HERO-CK-DB-1',
            'plan_id' => $a->id,
            'account_user_id' => $user->id,
            'status' => 'active',
            'auto_renew' => true,
        ]);

        $this->actingAs($user)->from(route('customer.membership.plan'))->post('/my/membership/plan', [
            'plan_id' => $b->id,
            'interval' => 'yearly',
        ])->assertRedirect(route('customer.membership.plan'))
            ->assertSessionHas('status');

        $this->assertSame($b->id, $membership->fresh()->plan_id);
    }
}
