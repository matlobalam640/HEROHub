<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Membership;
use App\Models\Plan;
use App\Models\User;
use App\Support\CompanyEnrollmentKind;
use App\Support\CorporateEnrollmentRequirement;
use Database\Seeders\CorporatePlansSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CorporateEnrollmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CorporatePlansSeeder::class);
    }

    public function test_business_user_can_view_corporate_enrollment_form(): void
    {
        [$user, $company] = $this->businessUserWithCompany();

        $this->actingAs($user)->get(route('business.enrollment'))
            ->assertOk()
            ->assertSee('HERO Client Rescue S.A Corporate Plans', false)
            ->assertSee('Workplace Coverage', false)
            ->assertSee('Manager Plans', false)
            ->assertSee('Executive Plans', false);
    }

    public function test_business_user_can_submit_corporate_enrollment_and_create_employees(): void
    {
        [$user, $company] = $this->businessUserWithCompany();

        $workplacePlan = Plan::query()->where('code', 'HC-02')->firstOrFail();
        $managerPlan = Plan::query()->where('code', 'HC-03A')->firstOrFail();

        $payload = [
            'contact_first_name' => 'Pat',
            'contact_last_name' => 'HR',
            'contact_position' => 'HR Director',
            'contact_phone' => '555-7000',
            'workplace_enrollments' => [[
                'first_name' => 'Alex',
                'last_name' => 'Worker',
                'plan_id' => $workplacePlan->id,
                'date_of_birth' => '1990-01-15',
            ]],
            'manager_enrollments' => [[
                'first_name' => 'Sam',
                'last_name' => 'Manager',
                'plan_id' => $managerPlan->id,
                'date_of_birth' => '1985-06-20',
            ]],
            'executive_enrollments' => [],
            'terms_accepted' => '1',
        ];

        $this->actingAs($user)->put(route('business.enrollment.update'), $payload)
            ->assertRedirect(route('business.enrollment'));

        $company->refresh();
        $this->assertTrue(CorporateEnrollmentRequirement::isComplete($company, CompanyEnrollmentKind::CORPORATE));
        $this->assertSame(2, Membership::query()->where('company_id', $company->id)->count());
        $this->assertDatabaseHas('company_enrollment_profiles', [
            'company_id' => $company->id,
            'contact_first_name' => 'Pat',
            'contact_last_name' => 'HR',
        ]);
    }

    public function test_corporate_portal_shows_enrollment_banner_when_incomplete(): void
    {
        [$user, $company] = $this->businessUserWithCompany();

        $this->actingAs($user)->get(route('business.portal'))
            ->assertOk()
            ->assertSee('Complete your corporate enrollment', false);
    }

    /**
     * @return array{0: User, 1: Company}
     */
    private function businessUserWithCompany(): array
    {
        $user = User::factory()->create();
        $user->assignRole('business');

        $company = Company::create([
            'name' => 'Acme Corporate Haiti',
            'billing_email' => 'billing@acme.ht',
            'owner_user_id' => $user->id,
        ]);

        return [$user, $company];
    }
}
