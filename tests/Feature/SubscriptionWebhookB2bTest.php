<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Membership;
use App\Models\Plan;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SubscriptionWebhookB2bTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        Mail::fake();
    }

    public function test_b2b_company_webhook_creates_company_and_billing_membership(): void
    {
        $plan = Plan::create([
            'code' => 'SMB_TEAM',
            'name' => 'SMB Team',
            'category' => 'business',
            'sort_order' => 1,
            'billing_interval' => 'monthly',
            'price' => 199.00,
            'currency' => 'USD',
            'active' => true,
        ]);

        $payload = [
            'subscription_id' => 'biz-sub-001',
            'status' => 'live',
            'billing_provider' => 'usa_payments',
            'record_type' => 'b2b_company',
            'plan_code' => 'SMB_TEAM',
            'company_name' => 'Acme Logistics',
            'start_date' => '2026-08-01',
            'current_term_ends_at' => '2027-08-01',
            'next_billing_at' => '2027-08-01',
            'last_billing_at' => '2026-08-01',
            'auto_collect' => 'true',
            'customer' => [
                'email' => 'hr@acme.test',
                'display_name' => 'Alice Owner',
                'first_name' => 'Alice',
                'last_name' => 'Owner',
                'phone' => '555-0100',
                'city' => 'Miami',
                'country' => 'US',
            ],
        ];

        $this->postJson('/api/v1/webhooks/subscription', $payload, [
            'X-Hero-Webhook-Secret' => 'test-webhook-secret-key',
        ])->assertOk()
            ->assertJsonPath('record_type', 'b2b_company')
            ->assertJsonPath('company_name', 'Acme Logistics');

        $company = Company::query()->where('name', 'Acme Logistics')->first();
        $this->assertNotNull($company);
        $this->assertSame('hr@acme.test', $company->ownerUser?->email);

        $membership = Membership::query()->where('billing_subscription_id', 'biz-sub-001')->first();
        $this->assertNotNull($membership);
        $this->assertSame($company->id, $membership->company_id);
        $this->assertSame($company->ownerUser?->id, $membership->account_user_id);
        $this->assertTrue($company->ownerUser?->hasRole('business'));
    }

    public function test_b2b_employee_webhook_creates_company_linked_membership(): void
    {
        $owner = User::factory()->create(['email' => 'hr@existing-co.test']);
        $owner->assignRole('business');

        $plan = Plan::create([
            'code' => 'HB-02',
            'name' => 'Workplace Mobile',
            'category' => 'business',
            'sort_order' => 2,
            'billing_interval' => 'yearly',
            'price' => 62.50,
            'currency' => 'USD',
            'active' => true,
        ]);

        $company = Company::create([
            'name' => 'Existing Co',
            'billing_email' => 'billing@existing-co.test',
            'owner_user_id' => $owner->id,
            'default_plan_id' => $plan->id,
        ]);

        $payload = [
            'subscription_id' => 'emp-sub-001',
            'status' => 'live',
            'billing_provider' => 'usa_payments',
            'record_type' => 'b2b_employee',
            'plan_code' => 'HB-02',
            'company_name' => 'Existing Co',
            'start_date' => '2026-08-01',
            'current_term_ends_at' => '2027-08-01',
            'customer' => [
                'email' => 'employee@existing-co.test',
                'display_name' => 'Bob Worker',
                'first_name' => 'Bob',
                'last_name' => 'Worker',
            ],
        ];

        $this->postJson('/api/v1/webhooks/subscription', $payload, [
            'X-Hero-Webhook-Secret' => 'test-webhook-secret-key',
        ])->assertOk()
            ->assertJsonPath('record_type', 'b2b_employee')
            ->assertJsonPath('company_id', $company->id);

        $membership = Membership::query()->where('billing_subscription_id', 'emp-sub-001')->first();
        $this->assertNotNull($membership);
        $this->assertSame($company->id, $membership->company_id);
        $this->assertNull($membership->account_user_id);
    }

    public function test_business_plan_without_company_name_is_rejected(): void
    {
        Plan::create([
            'code' => 'SMB_TEAM',
            'name' => 'SMB Team',
            'category' => 'business',
            'sort_order' => 1,
            'billing_interval' => 'monthly',
            'price' => 199.00,
            'currency' => 'USD',
            'active' => true,
        ]);

        $payload = [
            'subscription_id' => 'biz-sub-missing-co',
            'status' => 'live',
            'record_type' => 'b2b_company',
            'plan_code' => 'SMB_TEAM',
            'customer' => [
                'email' => 'hr@missing.test',
                'display_name' => 'No Company',
            ],
        ];

        $this->postJson('/api/v1/webhooks/subscription', $payload, [
            'X-Hero-Webhook-Secret' => 'test-webhook-secret-key',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['company_name']);
    }

    public function test_business_plan_infers_b2b_company_when_record_type_omitted(): void
    {
        Plan::create([
            'code' => 'ENTERPRISE',
            'name' => 'Enterprise',
            'category' => 'corporate',
            'sort_order' => 3,
            'billing_interval' => 'yearly',
            'price' => 899.00,
            'currency' => 'USD',
            'active' => true,
        ]);

        $payload = [
            'subscription_id' => 'biz-sub-inferred',
            'status' => 'live',
            'billing_provider' => 'usa_payments',
            'gateway_plan_id' => 'ENTERPRISE',
            'company_name' => 'Global Medical Group',
            'start_date' => '2026-08-01',
            'current_term_ends_at' => '2027-08-01',
            'customer' => [
                'email' => 'hr@global-med.test',
                'display_name' => 'Pat Admin',
            ],
        ];

        $this->postJson('/api/v1/webhooks/subscription', $payload, [
            'X-Hero-Webhook-Secret' => 'test-webhook-secret-key',
        ])->assertOk()
            ->assertJsonPath('record_type', 'b2b_company');

        $this->assertDatabaseHas('companies', ['name' => 'Global Medical Group']);
    }
}
