<?php

namespace Tests\Feature;

use App\Models\Membership;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerMembershipStripePlanCheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_update_plan_redirects_to_stripe_review_when_stripe_enabled(): void
    {
        config(['stripe.secret' => 'sk_test_fake_for_redirect_only']);

        $user = User::factory()->create();
        $user->assignRole('customer');

        $current = Plan::create([
            'code' => 'HR-ST-CUR',
            'name' => 'Current',
            'category' => 'business',
            'sort_order' => 1,
            'billing_interval' => 'monthly',
            'price' => 49,
            'currency' => 'USD',
            'active' => true,
        ]);

        $target = Plan::create([
            'code' => 'HR-ST-TGT',
            'name' => 'Enterprise Coverage',
            'category' => 'business',
            'sort_order' => 2,
            'billing_interval' => 'monthly',
            'price' => 899,
            'currency' => 'USD',
            'active' => true,
        ]);

        Membership::create([
            'membership_number' => 'HERO-ST-1',
            'plan_id' => $current->id,
            'account_user_id' => $user->id,
            'status' => 'active',
            'auto_renew' => true,
        ]);

        $response = $this->actingAs($user)->post('/my/membership/plan', [
            'plan_id' => $target->id,
            'interval' => 'monthly',
        ]);

        $response->assertRedirect();
        $this->assertStringContainsString('/my/membership/plan/stripe-checkout/', $response->headers->get('Location'));
    }

    public function test_subscribe_from_catalog_redirects_to_stripe_review_when_eligible(): void
    {
        config(['stripe.secret' => 'sk_test_fake_for_redirect_only']);

        $user = User::factory()->create();
        $user->assignRole('customer');

        $current = Plan::create([
            'code' => 'HR-ST-CUR2',
            'name' => 'Current',
            'category' => 'business',
            'sort_order' => 1,
            'billing_interval' => 'monthly',
            'price' => 49,
            'currency' => 'USD',
            'active' => true,
        ]);

        $target = Plan::create([
            'code' => 'HR-ST-TGT2',
            'name' => 'Target',
            'category' => 'business',
            'sort_order' => 2,
            'billing_interval' => 'monthly',
            'price' => 199,
            'currency' => 'USD',
            'active' => true,
        ]);

        Membership::create([
            'membership_number' => 'HERO-ST-SUB-1',
            'plan_id' => $current->id,
            'account_user_id' => $user->id,
            'status' => 'active',
            'auto_renew' => true,
        ]);

        $response = $this->actingAs($user)->get('/my/membership/plan/subscribe/'.$target->id.'/monthly');

        $response->assertRedirect();
        $this->assertStringContainsString('/my/membership/plan/stripe-checkout/', $response->headers->get('Location'));
    }

    public function test_stripe_review_rejects_invalid_token(): void
    {
        config(['stripe.secret' => 'sk_test_fake']);

        $user = User::factory()->create();
        $user->assignRole('customer');

        $this->actingAs($user)->get('/my/membership/plan/stripe-checkout/not-a-real-token')
            ->assertForbidden();
    }
}
