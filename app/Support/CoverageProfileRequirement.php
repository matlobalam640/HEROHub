<?php

namespace App\Support;

use App\Models\Member;
use App\Models\MemberDependent;
use App\Models\Membership;
use App\Models\MembershipCoverageProfile;
use App\Models\Plan;
use App\Models\User;

class CoverageProfileRequirement
{
    /** @var list<string> */
    public const INDIVIDUAL_FIELDS = [
        'first_name',
        'last_name',
        'date_of_birth',
        'gender',
        'phone',
        'id_number',
        'country',
        'city',
    ];

    /** @var list<string> */
    public const FAMILY_PRIMARY_FIELDS = [
        'first_name',
        'last_name',
        'date_of_birth',
        'gender',
        'phone',
        'email',
        'street',
        'city',
        'state',
        'zip_code',
        'country',
    ];

    public static function medicalConditionKeys(): array
    {
        return array_keys(CoverageFormTranslations::MEDICAL_CONDITIONS);
    }

    public static function usesFamilyForm(?Plan $plan): bool
    {
        return $plan !== null && $plan->requiresExtendedCoverageForm();
    }

    public static function membershipForUser(User $user): ?Membership
    {
        return Membership::query()
            ->with(['plan', 'members', 'dependents', 'coverageProfile'])
            ->where('account_user_id', $user->id)
            ->orderByDesc('id')
            ->first();
    }

    public static function primaryMember(Membership $membership): ?Member
    {
        $membership->loadMissing('members');

        return $membership->members->firstWhere('is_primary', true)
            ?? $membership->members->first();
    }

    /**
     * @return list<MemberDependent>
     */
    public static function householdDependents(Membership $membership): array
    {
        $membership->loadMissing('dependents');

        return $membership->dependents
            ->filter(fn (MemberDependent $dep) => ($dep->relationship ?? '') !== 'visitor')
            ->values()
            ->all();
    }

    public static function isComplete(Membership $membership, ?Member $primary = null): bool
    {
        $primary ??= self::primaryMember($membership);
        $plan = $membership->plan;

        if (self::usesFamilyForm($plan)) {
            return self::missingFieldLabels($membership, $primary) === [];
        }

        return self::missingFieldKeys($primary, self::INDIVIDUAL_FIELDS) === [];
    }

    /**
     * @return list<string>
     */
    public static function missingFieldLabels(Membership $membership, ?Member $primary = null): array
    {
        $primary ??= self::primaryMember($membership);
        $plan = $membership->plan;

        if (! self::usesFamilyForm($plan)) {
            return array_values(array_map(
                fn (string $key) => CoverageFormTranslations::en($key),
                self::missingFieldKeys($primary, self::INDIVIDUAL_FIELDS),
            ));
        }

        $missing = [];
        foreach (self::missingFieldKeys($primary, self::FAMILY_PRIMARY_FIELDS) as $key) {
            $missing[] = CoverageFormTranslations::en($key);
        }

        $profile = $membership->coverageProfile;
        if (! $profile) {
            $missing[] = CoverageFormTranslations::en('emergency_contact');
            $missing[] = CoverageFormTranslations::en('insurance_section');
            $missing[] = CoverageFormTranslations::en('medical_section');
            $missing[] = CoverageFormTranslations::en('photo_id_document');
            $missing[] = CoverageFormTranslations::en('passport_document');
            $missing[] = CoverageFormTranslations::en('terms_section');
        } else {
            if (blank($profile->emergency_contact_first_name) || blank($profile->emergency_contact_last_name) || blank($profile->emergency_contact_phone)) {
                $missing[] = CoverageFormTranslations::en('emergency_contact');
            }
            if (blank($profile->insurance_company) || blank($profile->insurance_policy_number) || blank($profile->insurance_provider_phone)) {
                $missing[] = CoverageFormTranslations::en('insurance_section');
            }
            if (blank($profile->blood_type) || blank($profile->allergies)) {
                $missing[] = CoverageFormTranslations::en('medical_section');
            }
            if (blank($profile->photo_id_path)) {
                $missing[] = CoverageFormTranslations::en('photo_id_document');
            }
            if (blank($profile->passport_path)) {
                $missing[] = CoverageFormTranslations::en('passport_document');
            }
            if ($profile->terms_accepted_at === null) {
                $missing[] = CoverageFormTranslations::en('terms_section');
            }
        }

        $completeDependents = collect(self::householdDependents($membership))
            ->filter(fn (MemberDependent $dep) => self::dependentIsComplete($dep))
            ->count();

        if ($completeDependents < 1) {
            $missing[] = CoverageFormTranslations::en('at_least_one_dependent');
        }

        return array_values(array_unique($missing));
    }

    public static function shouldPromptUser(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        $membership = self::membershipForUser($user);
        if (! $membership) {
            return false;
        }

        if (! $user->hasRole('customer') && ! $membership) {
            return false;
        }

        return ! self::isComplete($membership);
    }

    public static function dependentIsComplete(MemberDependent $dependent): bool
    {
        return ! blank($dependent->first_name)
            && ! blank($dependent->last_name)
            && $dependent->date_of_birth !== null
            && ! blank($dependent->gender)
            && ! blank($dependent->relationship);
    }

    /**
     * @param  list<string>  $fields
     * @return list<string>
     */
    private static function missingFieldKeys(?Member $primary, array $fields): array
    {
        if (! $primary) {
            return $fields;
        }

        $missing = [];
        foreach ($fields as $field) {
            if (! self::fieldIsFilled($primary, $field)) {
                $missing[] = $field;
            }
        }

        return $missing;
    }

    private static function fieldIsFilled(Member $primary, string $field): bool
    {
        if ($field === 'date_of_birth') {
            return $primary->date_of_birth !== null;
        }

        $value = $primary->{$field} ?? null;

        return is_string($value) && trim($value) !== '';
    }
}
