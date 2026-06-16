<?php

namespace Tests\Feature;

use App\Models\Membership;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerMembershipFamilyDependentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_cannot_add_family_dependent_on_non_family_plan(): void
    {
        $user = User::factory()->create();
        $user->assignRole('customer');

        $plan = Plan::create([
            'code' => 'HR-TEST-IND',
            'name' => 'Test individual',
            'category' => 'retail',
            'retail_subgroup' => 'annual_individual',
            'sort_order' => 1,
            'billing_interval' => 'yearly',
            'price' => 100,
            'currency' => 'USD',
            'active' => true,
        ]);

        Membership::create([
            'membership_number' => 'HERO-TEST-IND-1',
            'plan_id' => $plan->id,
            'account_user_id' => $user->id,
            'status' => 'active',
            'auto_renew' => true,
        ]);

        $this->actingAs($user)->post('/my/membership/dependents', [
            'first_name' => 'Sam',
            'last_name' => 'Case',
            'relationship' => 'child',
        ])->assertForbidden();
    }

    public function test_can_add_family_dependent_on_annual_family_plan(): void
    {
        $user = User::factory()->create();
        $user->assignRole('customer');

        $plan = Plan::create([
            'code' => 'HR-TEST-FAM',
            'name' => 'Test family',
            'category' => 'retail',
            'retail_subgroup' => 'annual_family',
            'sort_order' => 2,
            'billing_interval' => 'yearly',
            'price' => 300,
            'currency' => 'USD',
            'active' => true,
            'included_members' => 4,
        ]);

        $membership = Membership::create([
            'membership_number' => 'HERO-TEST-FAM-1',
            'plan_id' => $plan->id,
            'account_user_id' => $user->id,
            'status' => 'active',
            'auto_renew' => true,
        ]);

        $this->actingAs($user)->post('/my/membership/dependents', [
            'first_name' => 'Sam',
            'last_name' => 'Case',
            'relationship' => 'child',
        ])->assertRedirect();

        $this->assertDatabaseHas('member_dependents', [
            'membership_id' => $membership->id,
            'first_name' => 'Sam',
            'last_name' => 'Case',
            'relationship' => 'child',
        ]);
    }
}
