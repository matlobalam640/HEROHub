<?php

namespace Tests\Feature;

use App\Models\Membership;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SubscriptionWebhookGatewayPlanTest extends TestCase
{
    use RefreshDatabase;

    public function test_syncs_membership_when_payload_uses_gateway_plan_id(): void
    {
        Plan::create([
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

        $payload = [
            'subscription_id' => '12362239268',
            'status' => 'live',
            'billing_provider' => 'usa_payments',
            'gateway_plan_id' => 'HR-02Y',
            'start_date' => '2026-07-27',
            'current_term_ends_at' => '2027-07-27',
            'next_billing_at' => '2027-07-27',
            'last_billing_at' => '2026-07-27',
            'auto_collect' => 'true',
            'customer' => [
                'email' => 'gateway-sync@example.com',
                'display_name' => 'Gateway User',
                'phone' => '555-0101',
            ],
            'payment' => [
                'transaction_id' => '12362239168',
                'subscription_id' => '12362239268',
                'gateway_plan_id' => 'HR-02Y',
                'response_code' => '100',
                'amount' => 218.99,
                'tax' => 19.99,
                'currency' => 'USD',
            ],
        ];

        $this->postJson('/api/v1/webhooks/subscription', $payload, [
            'X-Hero-Webhook-Secret' => 'test-webhook-secret-key',
        ])->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('plan_code', 'HR-02');

        $this->assertDatabaseHas('memberships', [
            'billing_subscription_id' => '12362239268',
            'billing_provider' => 'usa_payments',
            'status' => 'active',
        ]);

        $user = User::where('email', 'gateway-sync@example.com')->first();
        $this->assertNotNull($user);
    }
}
