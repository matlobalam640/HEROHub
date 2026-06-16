<?php

namespace Tests\Feature;

use App\Models\Membership;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerMembershipFamilyPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_family_members_page_requires_membership(): void
    {
        $user = User::factory()->create();
        $user->assignRole('customer');

        $this->actingAs($user)->get('/my/membership/family-members')->assertNotFound();
    }

    public function test_family_members_page_ok_with_membership(): void
    {
        $user = User::factory()->create();
        $user->assignRole('customer');

        $plan = Plan::create([
            'code' => 'HR-TF',
            'name' => 'Test family plan',
            'category' => 'retail',
            'retail_subgroup' => 'annual_family',
            'sort_order' => 1,
            'billing_interval' => 'yearly',
            'price' => 100,
            'currency' => 'USD',
            'active' => true,
        ]);

        Membership::create([
            'membership_number' => 'HERO-FAM-PAGE-1',
            'plan_id' => $plan->id,
            'account_user_id' => $user->id,
            'status' => 'active',
            'auto_renew' => true,
        ]);

        $this->actingAs($user)->get('/my/membership/family-members')
            ->assertOk()
            ->assertSee('Family members', false)
            ->assertSee('Add family member', false);
    }
}
