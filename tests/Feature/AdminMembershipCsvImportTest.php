<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Membership;
use App\Models\Plan;
use App\Models\User;
use App\Services\MembershipCsvImportService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminMembershipCsvImportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    private function createRetailPlan(): Plan
    {
        return Plan::create([
            'code' => 'HR-02',
            'name' => 'Local annual',
            'category' => 'retail',
            'retail_subgroup' => 'annual_individual',
            'sort_order' => 1,
            'billing_interval' => 'yearly',
            'price' => 199.99,
            'currency' => 'USD',
            'active' => true,
        ]);
    }

    private function createBusinessPlan(): Plan
    {
        return Plan::create([
            'code' => 'SMB_TEAM',
            'name' => 'SMB Team',
            'category' => 'business',
            'retail_subgroup' => null,
            'sort_order' => 2,
            'billing_interval' => 'monthly',
            'price' => 49.99,
            'currency' => 'USD',
            'active' => true,
        ]);
    }

    public function test_admin_can_download_template_csv(): void
    {
        $admin = User::factory()->create(['password' => Hash::make('password')]);
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->get(route('admin.migration.template'))
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_import_auto_generates_membership_number_and_creates_records(): void
    {
        $this->createRetailPlan();

        $service = app(MembershipCsvImportService::class);
        $rows = [[
            'line' => 2,
            'record_type' => 'b2c',
            'email' => 'legacy.member@example.com',
            'first_name' => 'Legacy',
            'last_name' => 'Member',
            'plan_code' => 'HR-02',
            'membership_number' => '',
            'status' => 'active',
            'coverage_start' => '2026-01-01',
            'coverage_end' => '2027-01-01',
            'phone' => '',
            'billing_provider' => 'manual',
            'billing_subscription_id' => '',
            'auto_renew' => 'yes',
        ]];

        $result = $service->importRows($rows);

        $this->assertSame(1, $result['created_users']);
        $this->assertSame(1, $result['created_memberships']);
        $membership = Membership::query()->whereHas('accountUser', fn ($q) => $q->where('email', 'legacy.member@example.com'))->first();
        $this->assertNotNull($membership);
        $this->assertNull($membership->company_id);
        $this->assertMatchesRegularExpression('/^HERO-IMP-\d{4}-\d{6}$/', $membership->membership_number);
    }

    public function test_b2b_company_and_employee_import_creates_company_and_employee_membership(): void
    {
        $this->createBusinessPlan();

        $service = app(MembershipCsvImportService::class);
        $rows = [
            [
                'line' => 2,
                'record_type' => 'b2b_company',
                'email' => 'hr@acme.test',
                'first_name' => 'Alice',
                'last_name' => 'Owner',
                'plan_code' => 'SMB_TEAM',
                'company_name' => 'Acme Logistics',
                'company_billing_email' => 'billing@acme.test',
            ],
            [
                'line' => 3,
                'record_type' => 'b2b_employee',
                'email' => '',
                'first_name' => 'Bob',
                'last_name' => 'Worker',
                'plan_code' => 'SMB_TEAM',
                'company_name' => 'Acme Logistics',
            ],
        ];

        $result = $service->importRows($rows);

        $this->assertSame(1, $result['created_companies']);
        $this->assertSame(1, $result['created_users']);
        $this->assertSame(1, $result['created_memberships']);

        $company = Company::query()->where('name', 'Acme Logistics')->first();
        $this->assertNotNull($company);
        $this->assertSame('hr@acme.test', $company->ownerUser?->email);

        $employeeMembership = Membership::query()->where('company_id', $company->id)->first();
        $this->assertNotNull($employeeMembership);
        $this->assertNull($employeeMembership->account_user_id);
    }

    public function test_duplicate_email_in_file_is_rejected_on_import(): void
    {
        $this->createRetailPlan();

        $service = app(MembershipCsvImportService::class);
        $rows = [
            [
                'line' => 2,
                'record_type' => 'b2c',
                'email' => 'dup@example.com',
                'first_name' => 'One',
                'last_name' => 'Member',
                'plan_code' => 'HR-02',
            ],
            [
                'line' => 3,
                'record_type' => 'b2c',
                'email' => 'dup@example.com',
                'first_name' => 'Two',
                'last_name' => 'Member',
                'plan_code' => 'HR-02',
            ],
        ];

        $analysis = $service->analyzeRows($rows);
        $this->assertSame(2, $analysis['summary']['error']);

        $result = $service->importRows($rows);
        $this->assertSame(0, $result['created_memberships']);
        $this->assertGreaterThan(0, $result['errors']);
    }

    public function test_admin_can_preview_uploaded_csv(): void
    {
        $this->createRetailPlan();

        $admin = User::factory()->create(['password' => Hash::make('password')]);
        $admin->assignRole('admin');

        $csv = "record_type,email,first_name,last_name,plan_code\nb2c,preview@example.com,Jane,Doe,HR-02\n";
        $file = UploadedFile::fake()->createWithContent('members.csv', $csv);

        $this->actingAs($admin)
            ->post(route('admin.migration.preview'), ['file' => $file])
            ->assertRedirect(route('admin.migration.index'))
            ->assertSessionHas('status');

        $this->actingAs($admin)
            ->get(route('admin.migration.index'))
            ->assertOk()
            ->assertSee('preview@example.com', false);
    }

    public function test_admin_can_view_companies_index(): void
    {
        $admin = User::factory()->create(['password' => Hash::make('password')]);
        $admin->assignRole('admin');

        Company::create([
            'name' => 'Listed Co',
            'billing_email' => 'billing@listed.test',
            'owner_user_id' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.companies.index'))
            ->assertOk()
            ->assertSee('Listed Co', false);
    }
}
