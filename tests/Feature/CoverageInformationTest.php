<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\Membership;
use App\Models\Plan;
use App\Models\User;
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

        $this->actingAs($user)->put(route('customer.membership.coverage.update'), [
            'first_name' => 'Alex',
            'last_name' => 'Member',
            'date_of_birth' => '1990-05-15',
            'gender' => 'female',
            'phone' => '555-0100',
            'id_number' => 'P1234567',
            'country' => 'USA',
            'city' => 'Miami',
        ])->assertRedirect(route('customer.membership.coverage'));

        $this->assertDatabaseHas('members', [
            'membership_id' => $membership->id,
            'is_primary' => 1,
            'id_number' => 'P1234567',
            'city' => 'Miami',
        ]);

        $this->actingAs($user)->get(route('customer.membership'))
            ->assertOk()
            ->assertDontSee('Complete your coverage information', false);
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
