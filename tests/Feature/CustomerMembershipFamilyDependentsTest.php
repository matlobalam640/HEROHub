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
            'date_of_birth' => '2015-06-01',
            'gender' => 'male',
            'relationship' => 'child',
        ])->assertRedirect();

        $this->assertDatabaseHas('member_dependents', [
            'membership_id' => $membership->id,
            'first_name' => 'Sam',
            'last_name' => 'Case',
            'relationship' => 'child',
        ]);
    }

    public function test_cannot_exceed_plan_dependent_limit(): void
    {
        $user = User::factory()->create();
        $user->assignRole('customer');

        $plan = Plan::create([
            'code' => 'HR-TEST-FAM-LIM',
            'name' => 'Test family limited',
            'category' => 'retail',
            'retail_subgroup' => 'annual_family',
            'sort_order' => 2,
            'billing_interval' => 'yearly',
            'price' => 300,
            'currency' => 'USD',
            'active' => true,
            'included_members' => 4,
            'max_members' => 4,
        ]);

        $membership = Membership::create([
            'membership_number' => 'HERO-TEST-FAM-LIM-1',
            'plan_id' => $plan->id,
            'account_user_id' => $user->id,
            'status' => 'active',
            'auto_renew' => true,
        ]);

        foreach (range(1, 3) as $index) {
            $membership->dependents()->create([
                'first_name' => 'Member',
                'last_name' => (string) $index,
                'date_of_birth' => '2015-01-0'.$index,
                'gender' => 'female',
                'relationship' => 'child',
            ]);
        }

        $this->actingAs($user)->post('/my/membership/dependents', [
            'first_name' => 'Extra',
            'last_name' => 'Member',
            'date_of_birth' => '2014-01-01',
            'gender' => 'male',
            'relationship' => 'child',
        ])->assertRedirect()
            ->assertSessionHasErrors('first_name');

        $this->assertSame(3, $membership->dependents()->count());
    }

    public function test_family_members_page_lists_added_dependents(): void
    {
        $user = User::factory()->create();
        $user->assignRole('customer');

        $plan = Plan::create([
            'code' => 'HR-TEST-FAM-LIST',
            'name' => 'Test family list',
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
            'membership_number' => 'HERO-TEST-FAM-LIST-1',
            'plan_id' => $plan->id,
            'account_user_id' => $user->id,
            'status' => 'active',
            'auto_renew' => true,
        ]);

        $membership->dependents()->create([
            'first_name' => 'Jordan',
            'last_name' => 'Lee',
            'date_of_birth' => '1990-03-15',
            'gender' => 'female',
            'relationship' => 'spouse',
        ]);

        $this->actingAs($user)->get('/my/membership/family-members')
            ->assertOk()
            ->assertSee('Jordan Lee', false)
            ->assertSee('Spouse', false)
            ->assertSee('Date of birth', false)
            ->assertSee('Gender', false)
            ->assertSee('1 of 3 included', false);
    }
}
