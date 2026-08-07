<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\Membership;
use App\Models\Plan;
use App\Models\User;
use App\Support\CoverageProfileRequirement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CoverageInformationTest extends TestCase
{
    use RefreshDatabase;

    public function test_coverage_page_requires_membership(): void
    {
        $user = User::factory()->create();
        $user->assignRole('customer');

        $this->actingAs($user)->get('/my/membership/coverage-information')->assertNotFound();
    }

    public function test_incomplete_profile_shows_banner_on_membership_page(): void
    {
        [$user, $membership] = $this->createMembershipWithPrimary(firstName: 'Alex', lastName: 'Member');

        $this->actingAs($user)->get(route('customer.membership'))
            ->assertOk()
            ->assertSee('Complete your coverage information', false)
            ->assertSee('Complete now', false);
    }

    public function test_customer_can_save_coverage_information_and_banner_disappears(): void
    {
        [$user, $membership] = $this->createMembershipWithPrimary(firstName: 'Alex', lastName: 'Member');

        $healthAnswers = [];
        foreach (array_keys(\App\Support\CoverageFormTranslations::INDIVIDUAL_HEALTH_QUESTIONNAIRE) as $key) {
            $healthAnswers[$key] = 'None reported';
        }

        $payload = [
            'first_name' => 'Alex',
            'last_name' => 'Member',
            'date_of_birth' => '1990-05-15',
            'gender' => 'female',
            'phone' => '555-0100',
            'email' => $user->email,
            'street' => '100 Main Street',
            'city' => 'Miami',
            'country' => 'USA',
            'id_number' => 'P1234567',
            'primary_care_provider' => 'Dr. Smith at Miami Clinic',
            'emergency_contact_first_name' => 'Jamie',
            'emergency_contact_last_name' => 'Contact',
            'emergency_contact_relationship' => 'Spouse',
            'emergency_contact_phone' => '555-0101',
            'health_plan_provider' => 'Blue Cross',
            'health_insurer' => 'Global Health',
            'health_questionnaire' => $healthAnswers,
            'medical_condition_flags' => ['high_blood_pressure'],
            'terms_accepted' => '1',
        ];

        $this->actingAs($user)->put(route('customer.membership.coverage.update'), $payload)
            ->assertRedirect(route('customer.membership.coverage'));

        $this->assertDatabaseHas('members', [
            'membership_id' => $membership->id,
            'is_primary' => 1,
            'id_number' => 'P1234567',
            'city' => 'Miami',
            'street' => '100 Main Street',
        ]);

        $this->actingAs($user)->get(route('customer.membership'))
            ->assertOk()
            ->assertDontSee('Complete your coverage information', false);
    }

    public function test_individual_plan_shows_extended_application_form(): void
    {
        $user = User::factory()->create();
        $user->assignRole('customer');

        $plan = Plan::create([
            'code' => 'HR-01A',
            'name' => 'Individual (annual)',
            'category' => 'retail',
            'retail_subgroup' => 'annual_individual',
            'sort_order' => 1,
            'billing_interval' => 'yearly',
            'price' => 150,
            'currency' => 'USD',
            'active' => true,
        ]);

        Membership::create([
            'membership_number' => 'HERO-IND-COV-1',
            'plan_id' => $plan->id,
            'account_user_id' => $user->id,
            'status' => 'active',
            'auto_renew' => true,
        ]);

        $this->actingAs($user)->get(route('customer.membership.coverage'))
            ->assertOk()
            ->assertSee('Individual Plan', false)
            ->assertSee('Primary care provider', false)
            ->assertSee('Health plan provider', false)
            ->assertDontSee('Individual Plan VIP', false);
    }

    public function test_individual_plan_accepts_full_application(): void
    {
        $user = User::factory()->create();
        $user->assignRole('customer');

        $plan = Plan::create([
            'code' => 'HR-01A-SUB',
            'name' => 'Individual submit test',
            'category' => 'retail',
            'retail_subgroup' => 'annual_individual',
            'sort_order' => 1,
            'billing_interval' => 'yearly',
            'price' => 150,
            'currency' => 'USD',
            'active' => true,
        ]);

        $membership = Membership::create([
            'membership_number' => 'HERO-IND-SUB-1',
            'plan_id' => $plan->id,
            'account_user_id' => $user->id,
            'status' => 'active',
            'auto_renew' => true,
        ]);

        $healthAnswers = [];
        foreach (array_keys(\App\Support\CoverageFormTranslations::INDIVIDUAL_HEALTH_QUESTIONNAIRE) as $key) {
            $healthAnswers[$key] = 'No';
        }

        $payload = [
            'first_name' => 'Riley',
            'last_name' => 'Member',
            'date_of_birth' => '1991-08-03',
            'gender' => 'male',
            'phone' => '555-0700',
            'email' => $user->email,
            'street' => '50 Oak Avenue',
            'city' => 'Port-au-Prince',
            'country' => 'Haiti',
            'id_number' => 'ID998877',
            'primary_care_provider' => 'Dr. Laurent',
            'emergency_contact_first_name' => 'Sam',
            'emergency_contact_last_name' => 'Contact',
            'emergency_contact_relationship' => 'Sibling',
            'emergency_contact_phone' => '555-0701',
            'health_plan_provider' => 'Assurance Plus',
            'health_insurer' => 'National Health',
            'health_questionnaire' => $healthAnswers,
            'other_medical_info' => 'None',
            'terms_accepted' => '1',
        ];

        $this->actingAs($user)->put(route('customer.membership.coverage.update'), $payload)
            ->assertRedirect(route('customer.membership.coverage'));

        $profile = $membership->fresh()->coverageProfile;
        $this->assertNotNull($profile);
        $this->assertSame('Assurance Plus', $profile->health_plan_provider);
        $this->assertSame('Dr. Laurent', $profile->primary_care_provider);
        $this->assertNotNull($profile->terms_accepted_at);
        $this->assertTrue(CoverageProfileRequirement::isComplete($membership->fresh()));
    }

    public function test_family_plan_shows_extended_coverage_form(): void
    {
        $user = User::factory()->create();
        $user->assignRole('customer');

        $plan = Plan::create([
            'code' => 'HR-03',
            'name' => 'Local – Family (annual)',
            'category' => 'retail',
            'retail_subgroup' => 'annual_family',
            'sort_order' => 1,
            'billing_interval' => 'yearly',
            'price' => 325,
            'currency' => 'USD',
            'active' => true,
            'included_members' => 4,
        ]);

        Membership::create([
            'membership_number' => 'HERO-FAM-COV-1',
            'plan_id' => $plan->id,
            'account_user_id' => $user->id,
            'status' => 'active',
            'auto_renew' => true,
        ]);

        $this->actingAs($user)->get(route('customer.membership.coverage'))
            ->assertOk()
            ->assertSee('Family of 4 plan', false)
            ->assertSee('First name', false)
            ->assertSee('Passport', false)
            ->assertSee('Medical and evacuation insurance information', false);
    }

    public function test_vip_individual_plan_shows_extended_application_form(): void
    {
        $user = User::factory()->create();
        $user->assignRole('customer');

        $plan = Plan::create([
            'code' => 'HR-02C',
            'name' => 'VIP – Individual (annual)',
            'category' => 'retail',
            'retail_subgroup' => 'annual_individual',
            'tier' => 'vip',
            'sort_order' => 1,
            'billing_interval' => 'yearly',
            'price' => 398.98,
            'currency' => 'USD',
            'active' => true,
        ]);

        Membership::create([
            'membership_number' => 'HERO-VIP-COV-1',
            'plan_id' => $plan->id,
            'account_user_id' => $user->id,
            'status' => 'active',
            'auto_renew' => true,
        ]);

        $this->actingAs($user)->get(route('customer.membership.coverage'))
            ->assertOk()
            ->assertSee('Individual Plan VIP', false)
            ->assertSee('Health questionnaire', false)
            ->assertSee('Passport expiry date', false)
            ->assertDontSee('Dependents', false);
    }

    public function test_vip_10_day_plan_shows_travel_enrollment_form(): void
    {
        $user = User::factory()->create();
        $user->assignRole('customer');

        $plan = Plan::create([
            'code' => 'HR-01AC',
            'name' => 'VIP – Individual',
            'category' => 'retail',
            'retail_subgroup' => '10_day',
            'tier' => 'vip',
            'sort_order' => 1,
            'coverage_days' => 10,
            'billing_interval' => 'one_time',
            'price' => 41.50,
            'currency' => 'USD',
            'active' => true,
        ]);

        Membership::create([
            'membership_number' => 'HERO-VIP10-COV-1',
            'plan_id' => $plan->id,
            'account_user_id' => $user->id,
            'status' => 'active',
            'auto_renew' => true,
        ]);

        $this->actingAs($user)->get(route('customer.membership.coverage'))
            ->assertOk()
            ->assertSee('HERO 10 Day VIP Plan', false)
            ->assertSee('Mailing address', false)
            ->assertSee('Trips', false)
            ->assertSee('Continue', false)
            ->assertDontSee('Individual Plan VIP', false);
    }

    public function test_vip_10_day_plan_accepts_travel_enrollment(): void
    {
        $user = User::factory()->create();
        $user->assignRole('customer');

        $plan = Plan::create([
            'code' => 'HR-01AC-T',
            'name' => 'VIP – Individual 10-day test',
            'category' => 'retail',
            'retail_subgroup' => '10_day',
            'tier' => 'vip',
            'sort_order' => 1,
            'coverage_days' => 10,
            'billing_interval' => 'one_time',
            'price' => 41.50,
            'currency' => 'USD',
            'active' => true,
        ]);

        $membership = Membership::create([
            'membership_number' => 'HERO-VIP10-SUB-1',
            'plan_id' => $plan->id,
            'account_user_id' => $user->id,
            'status' => 'active',
            'auto_renew' => true,
        ]);

        $payload = [
            'first_name' => 'Jordan',
            'last_name' => 'Traveler',
            'date_of_birth' => '1992-04-12',
            'phone' => '555-0600',
            'email' => $user->email,
            'street' => '10 Local Street',
            'city' => 'Port-au-Prince',
            'zip_code' => '6110',
            'nationality' => 'Haiti',
            'id_number' => 'P9876543',
            'mailing_street' => '500 Mail Road',
            'mailing_city' => 'Miami',
            'mailing_state' => 'FL',
            'mailing_zip_code' => '33101',
            'mailing_country' => 'USA',
            'emergency_contact_first_name' => 'Casey',
            'emergency_contact_last_name' => 'Contact',
            'emergency_contact_phone' => '555-0601',
            'trips' => [[
                'from' => 'Miami',
                'price' => '350',
                'date' => now()->addWeek()->format('Y-m-d'),
            ]],
            'trip_total' => '350',
            'passport_issued_by' => 'USA',
            'allergies' => 'None',
            'applicant_signature' => 'Jordan Traveler',
            'signature_date' => now()->format('Y-m-d'),
            'terms_accepted' => '1',
        ];

        $this->actingAs($user)->put(route('customer.membership.coverage.update'), $payload)
            ->assertRedirect(route('customer.membership.coverage'));

        $profile = $membership->fresh()->coverageProfile;
        $this->assertNotNull($profile);
        $this->assertSame('USA', $profile->passport_issued_by);
        $this->assertSame('Jordan Traveler', $profile->applicant_signature);
        $this->assertTrue(CoverageProfileRequirement::tripDetailsAreComplete($profile->trip_details));
    }

    public function test_vip_individual_plan_accepts_full_application(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();
        $user->assignRole('customer');

        $plan = Plan::create([
            'code' => 'HR-01BC',
            'name' => 'VIP – Individual submit test',
            'category' => 'retail',
            'retail_subgroup' => 'annual_individual',
            'tier' => 'vip',
            'sort_order' => 1,
            'billing_interval' => 'yearly',
            'price' => 500,
            'currency' => 'USD',
            'active' => true,
        ]);

        $membership = Membership::create([
            'membership_number' => 'HERO-VIP-SUB-1',
            'plan_id' => $plan->id,
            'account_user_id' => $user->id,
            'status' => 'active',
            'auto_renew' => true,
        ]);

        $healthAnswers = [];
        foreach (array_keys(\App\Support\CoverageFormTranslations::HEALTH_QUESTIONNAIRE) as $key) {
            $healthAnswers[$key] = 'no';
        }

        $payload = [
            'first_name' => 'Taylor',
            'last_name' => 'Vip',
            'date_of_birth' => '1988-01-20',
            'gender' => 'female',
            'phone' => '555-0400',
            'email' => $user->email,
            'nationality' => 'USA',
            'id_number' => 'VIP123456',
            'passport_expiry_date' => now()->addYears(3)->format('Y-m-d'),
            'resident_status' => 'citizen',
            'measurement_unit' => 'metric',
            'height' => '170',
            'weight' => '68',
            'occupation' => 'Engineer',
            'street' => '200 VIP Ave',
            'city' => 'Miami',
            'state' => 'FL',
            'zip_code' => '33101',
            'country' => 'USA',
            'emergency_contact_first_name' => 'Jordan',
            'emergency_contact_last_name' => 'Contact',
            'emergency_contact_phone' => '555-0401',
            'insurance_company' => 'Global VIP Health',
            'insurance_policy_number' => 'VIP-POL-1',
            'insurance_provider_phone' => '555-0500',
            'blood_type' => 'A+',
            'allergies' => 'None',
            'health_questionnaire' => $healthAnswers,
            'terms_accepted' => '1',
        ];

        $this->actingAs($user)->put(route('customer.membership.coverage.update'), array_merge($payload, [
            'photo_id' => UploadedFile::fake()->create('photo-id.jpg', 100, 'image/jpeg'),
            'passport' => UploadedFile::fake()->create('passport.pdf', 100, 'application/pdf'),
        ]))->assertRedirect(route('customer.membership.coverage'));

        $this->assertDatabaseHas('members', [
            'membership_id' => $membership->id,
            'nationality' => 'USA',
            'id_number' => 'VIP123456',
        ]);

        $profile = $membership->fresh()->coverageProfile;
        $this->assertNotNull($profile);
        $this->assertSame('Engineer', $profile->occupation);
        $this->assertSame('metric', $profile->measurement_unit);
        $this->assertNotNull($profile->terms_accepted_at);
    }

    public function test_family_plan_accepts_identity_document_uploads(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();
        $user->assignRole('customer');

        $plan = Plan::create([
            'code' => 'HR-03U',
            'name' => 'Local – Family upload test',
            'category' => 'retail',
            'retail_subgroup' => 'annual_family',
            'sort_order' => 1,
            'billing_interval' => 'yearly',
            'price' => 325,
            'currency' => 'USD',
            'active' => true,
            'included_members' => 4,
        ]);

        $membership = Membership::create([
            'membership_number' => 'HERO-FAM-UP-1',
            'plan_id' => $plan->id,
            'account_user_id' => $user->id,
            'status' => 'active',
            'auto_renew' => true,
        ]);

        $payload = [
            'first_name' => 'Jamie',
            'last_name' => 'Family',
            'date_of_birth' => '1985-03-10',
            'gender' => 'female',
            'phone' => '555-0200',
            'email' => $user->email,
            'street' => '100 Main St',
            'city' => 'Miami',
            'state' => 'FL',
            'zip_code' => '33101',
            'country' => 'USA',
            'emergency_contact_first_name' => 'Alex',
            'emergency_contact_last_name' => 'Contact',
            'emergency_contact_phone' => '555-0201',
            'dependents' => [[
                'first_name' => 'Sam',
                'last_name' => 'Family',
                'date_of_birth' => '2015-06-01',
                'gender' => 'male',
                'relationship' => 'child',
            ]],
            'insurance_company' => 'Global Health',
            'insurance_policy_number' => 'POL-123',
            'insurance_provider_phone' => '555-0300',
            'blood_type' => 'O+',
            'allergies' => 'None',
            'terms_accepted' => '1',
        ];

        $this->actingAs($user)->put(route('customer.membership.coverage.update'), array_merge($payload, [
            'photo_id' => UploadedFile::fake()->create('photo-id.jpg', 100, 'image/jpeg'),
            'passport' => UploadedFile::fake()->create('passport.pdf', 100, 'application/pdf'),
        ]))->assertRedirect(route('customer.membership.coverage'));

        $profile = $membership->fresh()->coverageProfile;
        $this->assertNotNull($profile);
        $this->assertNotNull($profile->photo_id_path);
        $this->assertNotNull($profile->passport_path);
        Storage::disk('local')->assertExists($profile->photo_id_path);
        Storage::disk('local')->assertExists($profile->passport_path);
    }

    /**
     * @return array{0: User, 1: Membership}
     */
    private function createMembershipWithPrimary(string $firstName, string $lastName): array
    {
        $user = User::factory()->create();
        $user->assignRole('customer');

        $plan = Plan::create([
            'code' => 'HR-COV',
            'name' => 'Coverage test plan',
            'category' => 'retail',
            'retail_subgroup' => 'annual_individual',
            'sort_order' => 1,
            'billing_interval' => 'yearly',
            'price' => 100,
            'currency' => 'USD',
            'active' => true,
        ]);

        $membership = Membership::create([
            'membership_number' => 'HERO-COV-TEST-1',
            'plan_id' => $plan->id,
            'account_user_id' => $user->id,
            'status' => 'active',
            'auto_renew' => true,
        ]);

        Member::create([
            'membership_id' => $membership->id,
            'is_primary' => true,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $user->email,
            'qr_token' => '00000000-0000-4000-8000-000000000001',
        ]);

        return [$user, $membership];
    }
}
