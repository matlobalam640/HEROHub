<?php

namespace Tests\Feature;

use App\Mail\NewSubscriptionMembershipMail;
use App\Models\Membership;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ZohoSubscriptionWebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_rejects_request_without_secret_header(): void
    {
        $this->postJson('/api/v1/webhooks/zoho/subscription', ['subscription_id' => 'x'])
            ->assertStatus(401);
    }

    public function test_rejects_wrong_secret(): void
    {
        $this->postJson('/api/v1/webhooks/zoho/subscription', ['subscription_id' => 'x'], [
            'X-Hero-Zoho-Webhook-Secret' => 'wrong',
        ])->assertStatus(401);
    }

    public function test_syncs_membership_from_zoho_payload(): void
    {
        Mail::fake();

        $plan = Plan::create([
            'code' => 'HR-TEST',
            'name' => 'Test retail plan',
            'category' => 'retail',
            'retail_subgroup' => 'annual_family',
            'sort_order' => 1,
            'billing_interval' => 'yearly',
            'price' => 100,
            'price_monthly' => 8.33,
            'zoho_code_yearly' => 'HR-03CY',
            'currency' => 'USD',
            'active' => true,
        ]);

        $payload = [
            'subscription_id' => '6304056000000755001',
            'subscription_number' => 'SUB-00107',
            'status' => 'live',
            'start_date' => '2026-04-24',
            'current_term_ends_at' => '2027-04-24',
            'customer_id' => '6304056000000152937',
            'created_time' => '2026-04-24T12:34:26-0400',
            'next_billing_at' => '2027-04-24',
            'last_billing_at' => '2026-04-24',
            'auto_collect' => 'true',
            'line_items' => json_encode([
                [
                    'code' => 'HR-03CY',
                    'name' => '1 YEAR VIP - FAMILY - YEARLY PAYMENT',
                ],
            ]),
            'plan' => json_encode(['plan_code' => 'HR-03CY', 'price' => 637]),
            'customer' => json_encode([
                'customer_id' => '6304056000000152937',
                'email' => 'zoho-webhook-test@example.com',
                'display_name' => 'Katia Brezault',
            ]),
        ];

        $response = $this->postJson('/api/v1/webhooks/zoho/subscription', $payload, [
            'X-Hero-Zoho-Webhook-Secret' => 'test-zoho-webhook-secret-key',
        ]);

        $response->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('created', true)
            ->assertJsonPath('membership_number', 'ZOHO-SUB-00107')
            ->assertJsonPath('billing_next_billing_at', '2027-04-24')
            ->assertJsonPath('billing_last_billing_at', '2026-04-24')
            ->assertJsonPath('billing_auto_collect', true);

        $this->assertDatabaseHas('memberships', [
            'billing_subscription_id' => '6304056000000755001',
            'plan_id' => $plan->id,
            'billing_provider' => 'zoho',
            'status' => 'active',
            'billing_auto_collect' => 1,
        ]);

        $membership = Membership::where('billing_subscription_id', '6304056000000755001')->first();
        $this->assertNotNull($membership);
        $this->assertSame('2027-04-24', $membership->billing_next_billing_at?->toDateString());
        $this->assertSame('2026-04-24', $membership->billing_last_billing_at?->toDateString());
        $this->assertNotNull($membership->billing_subscription_created_at);

        $user = User::where('email', 'zoho-webhook-test@example.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole('customer'));

        Mail::assertQueued(NewSubscriptionMembershipMail::class, function (NewSubscriptionMembershipMail $mail): bool {
            return $mail->needsPasswordSetup === true
                && $mail->user->email === 'zoho-webhook-test@example.com'
                && $mail->passwordResetUrl !== null;
        });
    }

    public function test_updates_existing_membership_on_repeat_webhook(): void
    {
        Mail::fake();

        $plan = Plan::create([
            'code' => 'HR-TEST2',
            'name' => 'Test plan 2',
            'category' => 'retail',
            'retail_subgroup' => 'annual_family',
            'sort_order' => 2,
            'billing_interval' => 'yearly',
            'price' => 50,
            'price_monthly' => 4,
            'zoho_code_yearly' => 'HR-99Y',
            'currency' => 'USD',
            'active' => true,
        ]);

        $user = User::factory()->create(['email' => 'repeat@example.com']);

        Membership::create([
            'membership_number' => 'ZOHO-SUB-KEEP',
            'plan_id' => $plan->id,
            'account_user_id' => $user->id,
            'billing_subscription_id' => '6304056000000999001',
            'billing_provider' => 'zoho',
            'billing_customer_id' => 'cust-1',
            'status' => 'active',
            'auto_renew' => true,
        ]);

        $payload = [
            'subscription_id' => '6304056000000999001',
            'subscription_number' => 'SUB-NEW',
            'status' => 'cancelled',
            'start_date' => '2026-01-01',
            'current_term_ends_at' => '2027-01-01',
            'customer_id' => 'cust-1',
            'line_items' => json_encode([['code' => 'HR-99Y', 'name' => 'Plan']]),
            'plan' => json_encode(['plan_code' => 'HR-99Y']),
            'customer' => json_encode([
                'email' => 'repeat@example.com',
                'display_name' => 'Repeat User',
            ]),
        ];

        $this->postJson('/api/v1/webhooks/zoho/subscription', $payload, [
            'X-Hero-Zoho-Webhook-Secret' => 'test-zoho-webhook-secret-key',
        ])->assertOk()->assertJsonPath('created', false);

        $this->assertDatabaseHas('memberships', [
            'billing_subscription_id' => '6304056000000999001',
            'membership_number' => 'ZOHO-SUB-KEEP',
            'status' => 'cancelled',
        ]);

        Mail::assertNotQueued(NewSubscriptionMembershipMail::class);
    }
}
