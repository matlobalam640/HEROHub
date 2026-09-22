<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Membership;
use App\Models\Plan;
use App\Models\User;
use App\Services\BusinessEnrollmentService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class BusinessEnrollmentFlowTest extends TestCase
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

        Mail::fake();
    }

    private function createBusinessPlan(array $overrides = []): Plan
    {
        return Plan::create(array_merge([
            'code' => 'HB-01',
            'name' => 'Workplace Coverage - On-Site',
            'category' => 'business',
            'tier' => 'workplace',
            'sort_order' => 10,
            'billing_interval' => 'yearly',
            'price' => 32.00,
            'price_monthly' => 3.33,
            'min_members' => 25,
            'max_members' => null,
            'currency' => 'USD',
            'active' => true,
        ], $overrides));
    }

    public function test_admin_can_enroll_business_and_invite_hr(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->post(route('admin.business-enrollment.store'), [
                'company_name' => 'Acme Rescue Co',
                'company_city' => 'Miami',
                'company_country' => 'USA',
                'hr_first_name' => 'Pat',
                'hr_last_name' => 'Owner',
                'hr_email' => 'hr.owner@acme.test',
            ])
            ->assertRedirect(route('admin.business-enrollment.index'))
            ->assertSessionHas('status');

        $company = Company::query()->where('name', 'Acme Rescue Co')->first();
        $this->assertNotNull($company);
        $this->assertSame(BusinessEnrollmentService::STATUS_PENDING_PAYMENT, $company->enrollment_status);
        $this->assertTrue($company->ownerUser->hasRole('business'));
        $this->assertSame('hr.owner@acme.test', $company->ownerUser->email);
    }

    public function test_hr_can_complete_usa_payments_checkout_and_activate_company(): void
    {
        Http::fake([
            'usapayments.transactiongateway.com/*' => Http::response('response=1&response_code=100&transactionid=888777&subscription_id=SUB-BIZ-1'),
        ]);

        $plan = $this->createBusinessPlan();
        $owner = User::factory()->create(['email' => 'hr.pay@acme.test', 'name' => 'Pat Owner']);
        $owner->assignRole('business');

        $company = Company::create([
            'name' => 'Pay Co',
            'billing_email' => 'hr.pay@acme.test',
            'owner_user_id' => $owner->id,
            'enrollment_status' => BusinessEnrollmentService::STATUS_PENDING_PAYMENT,
        ]);

        $this->actingAs($owner)
            ->post(route('business.plan-checkout.store'), [
                'plan_id' => $plan->id,
                'interval' => 'yearly',
                'payment_token' => 'tok_biz_1',
                'first_name' => 'Pat',
                'last_name' => 'Owner',
                'email' => 'hr.pay@acme.test',
                'phone' => '555-0100',
                'country' => 'USA',
                'state' => 'FL',
                'street' => '1 Main',
                'city' => 'Miami',
                'zip_code' => '33101',
            ])
            ->assertRedirect(route('business.employees.index'))
            ->assertSessionHas('status');

        $company->refresh();
        $this->assertSame(BusinessEnrollmentService::STATUS_ACTIVE, $company->enrollment_status);
        $this->assertSame($plan->id, $company->default_plan_id);
        $this->assertSame(25, $company->seat_limit);
        $this->assertSame('usa_payments', $company->billing_provider);
        $this->assertSame('SUB-BIZ-1', $company->billing_subscription_id);
    }

    public function test_employee_csv_import_enforces_seat_limit_and_sends_invites(): void
    {
        $plan = $this->createBusinessPlan([
            'code' => 'HB-LIMIT',
            'min_members' => 2,
            'max_members' => 2,
        ]);

        $owner = User::factory()->create(['email' => 'hr.limit@acme.test']);
        $owner->assignRole('business');

        $company = Company::create([
            'name' => 'Limit Co',
            'billing_email' => 'hr.limit@acme.test',
            'owner_user_id' => $owner->id,
            'enrollment_status' => BusinessEnrollmentService::STATUS_ACTIVE,
            'activated_at' => now(),
            'default_plan_id' => $plan->id,
            'seat_limit' => 2,
        ]);

        $csv = "first_name,last_name,email\n"
            ."One,Worker,one@acme.test\n"
            ."Two,Worker,two@acme.test\n"
            ."Three,Worker,three@acme.test\n";

        $file = UploadedFile::fake()->createWithContent('employees.csv', $csv);

        $this->actingAs($owner)
            ->post(route('business.employees.import'), ['file' => $file])
            ->assertRedirect(route('business.employees.index'));

        $this->assertSame(0, Membership::query()->where('company_id', $company->id)->count());

        $csvOk = "first_name,last_name,email\n"
            ."One,Worker,one@acme.test\n"
            ."Two,Worker,two@acme.test\n";
        $fileOk = UploadedFile::fake()->createWithContent('employees-ok.csv', $csvOk);

        $this->actingAs($owner)
            ->post(route('business.employees.import'), ['file' => $fileOk])
            ->assertRedirect(route('business.employees.index'))
            ->assertSessionHas('status');

        $this->assertSame(2, Membership::query()->where('company_id', $company->id)->count());
        $this->assertTrue(User::query()->where('email', 'one@acme.test')->first()?->hasRole('customer'));
        $this->assertTrue(User::query()->where('email', 'two@acme.test')->first()?->hasRole('customer'));
    }

    public function test_pending_company_cannot_open_employees_page(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('business');
        Company::create([
            'name' => 'Pending Co',
            'owner_user_id' => $owner->id,
            'enrollment_status' => BusinessEnrollmentService::STATUS_PENDING_PAYMENT,
        ]);

        $this->actingAs($owner)
            ->get(route('business.employees.index'))
            ->assertRedirect(route('business.plan-checkout.show'));
    }
}
