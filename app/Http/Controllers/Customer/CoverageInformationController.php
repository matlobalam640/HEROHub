<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\MemberDependent;
use App\Models\Membership;
use App\Models\MembershipCoverageProfile;
use App\Support\CoverageFormTranslations;
use App\Support\CoverageProfileRequirement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CoverageInformationController extends Controller
{
    public function show(Request $request): View
    {
        $membership = $this->membershipFor($request);
        abort_unless($membership, 404);

        $primary = CoverageProfileRequirement::primaryMember($membership);
        $plan = $membership->plan;
        $usesFamilyForm = CoverageProfileRequirement::usesFamilyForm($plan);

        return view('customer.membership.coverage-information', [
            'membership' => $membership,
            'plan' => $plan,
            'primary' => $primary,
            'profile' => $membership->coverageProfile,
            'dependents' => CoverageProfileRequirement::householdDependents($membership),
            'missingFields' => CoverageProfileRequirement::missingFieldLabels($membership, $primary),
            'genderOptionKeys' => $this->genderOptionKeys(),
            'relationshipOptionKeys' => $this->relationshipOptionKeys(),
            'bloodTypeOptions' => $this->bloodTypeOptions(),
            'medicalConditionFlags' => CoverageFormTranslations::MEDICAL_CONDITIONS,
            'usesFamilyForm' => $usesFamilyForm,
            'formTitle' => $plan?->coverageFormTitle() ?? 'Coverage information',
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $membership = $this->membershipFor($request);
        abort_unless($membership, 404);

        if (CoverageProfileRequirement::usesFamilyForm($membership->plan)) {
            return $this->updateFamilyForm($request, $membership);
        }

        return $this->updateIndividualForm($request, $membership);
    }

    private function updateIndividualForm(Request $request, Membership $membership): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:80'],
            'last_name' => ['required', 'string', 'max:80'],
            'date_of_birth' => ['required', 'date', 'before:today', 'after:1900-01-01'],
            'gender' => ['required', 'string', 'in:female,male,other,prefer_not_to_say'],
            'phone' => ['required', 'string', 'max:30'],
            'id_number' => ['required', 'string', 'max:80'],
            'country' => ['required', 'string', 'max:80'],
            'city' => ['required', 'string', 'max:80'],
        ]);

        $this->savePrimaryMember($membership, $request, $validated, includeAddress: false);

        return redirect()
            ->route('customer.membership.coverage')
            ->with('status', 'Coverage information saved. Thank you — your profile is now complete.');
    }

    private function updateFamilyForm(Request $request, Membership $membership): RedirectResponse
    {
        $existingProfile = $membership->coverageProfile;

        $validated = $request->validate([
            'preferred_coverage_start_date' => ['nullable', 'date', 'after_or_equal:today'],
            'first_name' => ['required', 'string', 'max:80'],
            'last_name' => ['required', 'string', 'max:80'],
            'date_of_birth' => ['required', 'date', 'before:today', 'after:1900-01-01'],
            'gender' => ['required', 'string', 'in:female,male,other,prefer_not_to_say'],
            'phone' => ['required', 'string', 'max:30'],
            'email' => ['required', 'email', 'max:120'],
            'street' => ['required', 'string', 'max:160'],
            'street_line2' => ['nullable', 'string', 'max:160'],
            'city' => ['required', 'string', 'max:80'],
            'state' => ['required', 'string', 'max:80'],
            'zip_code' => ['required', 'string', 'max:20'],
            'country' => ['required', 'string', 'max:80'],
            'photo_id' => [
                Rule::requiredIf(fn () => blank($existingProfile?->photo_id_path)),
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,pdf',
                'max:5120',
            ],
            'passport' => [
                Rule::requiredIf(fn () => blank($existingProfile?->passport_path)),
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,pdf',
                'max:5120',
            ],
            'emergency_contact_first_name' => ['required', 'string', 'max:80'],
            'emergency_contact_last_name' => ['required', 'string', 'max:80'],
            'emergency_contact_phone' => ['required', 'string', 'max:30'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'dependents' => ['required', 'array', 'min:1'],
            'dependents.*.first_name' => ['required', 'string', 'max:80'],
            'dependents.*.last_name' => ['required', 'string', 'max:80'],
            'dependents.*.date_of_birth' => ['required', 'date', 'before:today', 'after:1900-01-01'],
            'dependents.*.gender' => ['required', 'string', 'in:female,male,other,prefer_not_to_say'],
            'dependents.*.relationship' => ['required', 'string', 'max:40'],
            'insurance_company' => ['required', 'string', 'max:120'],
            'insurance_policy_number' => ['required', 'string', 'max:80'],
            'insurance_effective_start' => ['nullable', 'date'],
            'insurance_effective_end' => ['nullable', 'date', 'after_or_equal:insurance_effective_start'],
            'insurance_member_id' => ['nullable', 'string', 'max:80'],
            'insurance_policy_holder_name' => ['nullable', 'string', 'max:120'],
            'insurance_policy_holder_relationship' => ['nullable', 'string', 'max:80'],
            'insurance_beneficiary_name' => ['nullable', 'string', 'max:120'],
            'insurance_beneficiary_relationship' => ['nullable', 'string', 'max:80'],
            'insurance_provider_phone' => ['required', 'string', 'max:30'],
            'insurance_plan_type' => ['nullable', 'string', 'max:80'],
            'medevac_max_benefit' => ['nullable', 'string', 'max:40'],
            'medevac_policy_number' => ['nullable', 'string', 'max:80'],
            'blood_type' => ['required', 'string', 'max:20'],
            'allergies' => ['required', 'string', 'max:500'],
            'chronic_conditions' => ['nullable', 'string', 'max:5000'],
            'medical_condition_flags' => ['nullable', 'array'],
            'medical_condition_flags.*' => ['string', 'in:'.implode(',', CoverageProfileRequirement::medicalConditionKeys())],
            'other_medical_info' => ['nullable', 'string', 'max:5000'],
            'terms_accepted' => ['accepted'],
        ]);

        $photoPath = $existingProfile?->photo_id_path;
        $passportPath = $existingProfile?->passport_path;

        if ($request->hasFile('photo_id')) {
            if (is_string($photoPath) && $photoPath !== '') {
                Storage::disk('local')->delete($photoPath);
            }
            $photoPath = $request->file('photo_id')->store('membership-coverage/'.$membership->id, 'local');
        }

        if ($request->hasFile('passport')) {
            if (is_string($passportPath) && $passportPath !== '') {
                Storage::disk('local')->delete($passportPath);
            }
            $passportPath = $request->file('passport')->store('membership-coverage/'.$membership->id, 'local');
        }

        DB::transaction(function () use ($membership, $request, $validated, $photoPath, $passportPath) {
            $this->savePrimaryMember($membership, $request, $validated, includeAddress: true);

            MembershipCoverageProfile::query()->updateOrCreate(
                ['membership_id' => $membership->id],
                [
                    'preferred_coverage_start_date' => $validated['preferred_coverage_start_date'] ?? null,
                    'emergency_contact_first_name' => $validated['emergency_contact_first_name'],
                    'emergency_contact_last_name' => $validated['emergency_contact_last_name'],
                    'emergency_contact_phone' => $validated['emergency_contact_phone'],
                    'notes' => $validated['notes'] ?? null,
                    'photo_id_path' => $photoPath,
                    'passport_path' => $passportPath,
                    'insurance_company' => $validated['insurance_company'],
                    'insurance_policy_number' => $validated['insurance_policy_number'],
                    'insurance_effective_start' => $validated['insurance_effective_start'] ?? null,
                    'insurance_effective_end' => $validated['insurance_effective_end'] ?? null,
                    'insurance_member_id' => $validated['insurance_member_id'] ?? null,
                    'insurance_policy_holder_name' => $validated['insurance_policy_holder_name'] ?? null,
                    'insurance_policy_holder_relationship' => $validated['insurance_policy_holder_relationship'] ?? null,
                    'insurance_beneficiary_name' => $validated['insurance_beneficiary_name'] ?? null,
                    'insurance_beneficiary_relationship' => $validated['insurance_beneficiary_relationship'] ?? null,
                    'insurance_provider_phone' => $validated['insurance_provider_phone'],
                    'insurance_plan_type' => $validated['insurance_plan_type'] ?? null,
                    'medevac_max_benefit' => $validated['medevac_max_benefit'] ?? null,
                    'medevac_policy_number' => $validated['medevac_policy_number'] ?? null,
                    'blood_type' => $validated['blood_type'],
                    'allergies' => $validated['allergies'],
                    'chronic_conditions' => $validated['chronic_conditions'] ?? null,
                    'medical_condition_flags' => array_values($validated['medical_condition_flags'] ?? []),
                    'other_medical_info' => $validated['other_medical_info'] ?? null,
                    'terms_accepted_at' => now(),
                ]
            );

            $membership->dependents()
                ->where('relationship', '!=', 'visitor')
                ->delete();

            foreach ($validated['dependents'] as $dependent) {
                MemberDependent::query()->create([
                    'membership_id' => $membership->id,
                    'first_name' => trim($dependent['first_name']),
                    'last_name' => trim($dependent['last_name']),
                    'date_of_birth' => $dependent['date_of_birth'],
                    'gender' => $dependent['gender'],
                    'relationship' => trim($dependent['relationship']),
                ]);
            }
        });

        return redirect()
            ->route('customer.membership.coverage')
            ->with('status', 'Family coverage application saved. Thank you — your profile is now complete.');
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function savePrimaryMember(Membership $membership, Request $request, array $validated, bool $includeAddress): Member
    {
        $primary = Member::query()->firstOrNew([
            'membership_id' => $membership->id,
            'is_primary' => true,
        ]);

        $primary->fill([
            'first_name' => trim($validated['first_name']),
            'last_name' => trim($validated['last_name']),
            'date_of_birth' => $validated['date_of_birth'],
            'gender' => $validated['gender'],
            'phone' => trim($validated['phone']),
            'email' => trim((string) ($validated['email'] ?? $primary->email ?? $request->user()->email)),
            'country' => trim($validated['country']),
            'city' => trim($validated['city']),
            'id_number' => trim((string) ($validated['id_number'] ?? $primary->id_number ?? '')) ?: $primary->id_number,
        ]);

        if ($includeAddress) {
            $primary->fill([
                'street' => trim($validated['street']),
                'street_line2' => trim((string) ($validated['street_line2'] ?? '')) ?: null,
                'state' => trim($validated['state']),
                'zip_code' => trim($validated['zip_code']),
            ]);
        }

        if (! $primary->qr_token) {
            $primary->qr_token = (string) Str::uuid();
        }

        $primary->save();

        return $primary;
    }

    private function membershipFor(Request $request): ?Membership
    {
        return CoverageProfileRequirement::membershipForUser($request->user());
    }

    /**
     * @return array<string, string>
     */
    private function genderOptionKeys(): array
    {
        return [
            'female' => 'gender_female',
            'male' => 'gender_male',
            'other' => 'gender_other',
            'prefer_not_to_say' => 'gender_prefer_not',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function relationshipOptionKeys(): array
    {
        return [
            'spouse' => 'relationship_spouse',
            'child' => 'relationship_child',
            'step_child' => 'relationship_step_child',
            'foster_child' => 'relationship_foster_child',
            'other' => 'relationship_other',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function genderOptions(): array
    {
        return [
            'female' => 'Female',
            'male' => 'Male',
            'other' => 'Other',
            'prefer_not_to_say' => 'Prefer not to say',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function relationshipOptions(): array
    {
        return [
            'spouse' => 'Spouse',
            'child' => 'Child',
            'step_child' => 'Step child',
            'foster_child' => 'Foster child',
            'other' => 'Other',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function bloodTypeOptions(): array
    {
        return [
            'A+' => 'A+',
            'A-' => 'A-',
            'B+' => 'B+',
            'B-' => 'B-',
            'AB+' => 'AB+',
            'AB-' => 'AB-',
            'O+' => 'O+',
            'O-' => 'O-',
            'unknown' => 'Unknown',
        ];
    }
}
