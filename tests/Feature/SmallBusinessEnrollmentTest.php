<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Membership;
use App\Models\Plan;
use App\Models\User;
use App\Support\CompanyEnrollmentKind;
use App\Support\CorporateEnrollmentRequirement;
use Database\Seeders\SmallBusinessPlansSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SmallBusinessEnrollmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(SmallBusinessPlansSeeder::class);
    }

    public function test_business_user_can_view_small_business_enrollment_form(): void
    {
        [$user] = $this->businessUserWithCompany();

        $this->actingAs($user)->get(route('business.small-business.enrollment'))
            ->assertOk()
            ->assertSee('Hero Client Rescue S.A Small Business Plans', false)
            ->assertSee('On-Site Workplace Coverage', false)
            ->assertSee('Manager Plan', false)
            ->assertDontSee('Executive Plans', false);
    }

    public function test_business_user_can_submit_small_business_enrollment(): void
    {
        [$user, $company] = $this->businessUserWithCompany();

        $workplacePlan = Plan::query()->where('code', 'HB-01')->firstOrFail();
        $managerPlan = Plan::query()->where('code', 'HB-03A')->firstOrFail();

        $payload = [
            'business_name' => 'Blue Desk Services',
            'contact_first_name' => 'Riley',
            'contact_last_name' => 'Owner',
            'contact_position' => 'Owner',
            'contact_phone' => '555-8000',
            'workplace_enrollments' => [[
                'first_name' => 'Jamie',
                'last_name' => 'Staff',
                'plan_id' => $workplacePlan->id,
                'date_of_birth' => '1993-02-10',
            ]],
            'manager_enrollments' => [[
                'first_name' => 'Chris',
                'last_name' => 'Manager',
                'plan_id' => $managerPlan->id,
                'date_of_birth' => '1988-08-03',
            ]],
            'terms_accepted' => '1',
        ];

        $this->actingAs($user)->put(route('business.small-business.enrollment.update'), $payload)
            ->assertRedirect(route('business.small-business.enrollment'));

        $company->refresh();
        $this->assertTrue(CorporateEnrollmentRequirement::isComplete($company, CompanyEnrollmentKind::SMALL_BUSINESS));
        $this->assertSame('Blue Desk Services', $company->name);
        $this->assertSame(2, Membership::query()->where('company_id', $company->id)->count());
        $this->assertDatabaseHas('company_enrollment_profiles', [
            'company_id' => $company->id,
            'business_name' => 'Blue Desk Services',
        ]);
    }

    public function test_portal_shows_small_business_enrollment_banner_when_incomplete(): void
    {
        [$user] = $this->businessUserWithCompany();

        $this->actingAs($user)->get(route('business.portal'))
            ->assertOk()
            ->assertSee('Complete your small business enrollment', false);
    }

    /**
     * @return array{0: User, 1: Company}
     */
    private function businessUserWithCompany(): array
    {
        $user = User::factory()->create();
        $user->assignRole('business');

        $company = Company::create([
            'name' => 'Pending Small Business Co',
            'billing_email' => 'billing@small.ht',
            'owner_user_id' => $user->id,
        ]);

        return [$user, $company];
    }
}
