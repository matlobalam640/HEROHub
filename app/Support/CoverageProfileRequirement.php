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

    /** @var list<string> */
    public const VIP_PRIMARY_FIELDS = [
        'first_name',
        'last_name',
        'date_of_birth',
        'gender',
        'phone',
        'email',
        'nationality',
        'id_number',
        'passport_expiry_date',
        'street',
        'city',
        'state',
        'zip_code',
        'country',
    ];

    public static function healthQuestionnaireKeys(): array
    {
        return array_keys(CoverageFormTranslations::HEALTH_QUESTIONNAIRE);
    }

    public static function usesFamilyForm(?Plan $plan): bool
    {
        return $plan !== null && $plan->coverageFormVariant() === 'family';
    }

    /** @var list<string> */
    public const VIP_10_DAY_PRIMARY_FIELDS = [
        'first_name',
        'last_name',
        'date_of_birth',
        'phone',
        'email',
        'street',
        'city',
        'zip_code',
        'nationality',
        'id_number',
    ];

    public static function travelPreferenceKeys(): array
    {
        return array_keys(CoverageFormTranslations::TRAVEL_PREFERENCES);
    }

    public static function usesVip10DayForm(?Plan $plan): bool
    {
        return $plan !== null && $plan->coverageFormVariant() === 'vip_10_day';
    }

    /** @var list<string> */
    public const INDIVIDUAL_PLAN_PRIMARY_FIELDS = [
        'first_name',
        'last_name',
        'date_of_birth',
        'gender',
        'phone',
        'email',
        'street',
        'city',
        'country',
        'id_number',
    ];

    public static function individualHealthQuestionnaireKeys(): array
    {
        return array_keys(CoverageFormTranslations::INDIVIDUAL_HEALTH_QUESTIONNAIRE);
    }

    public static function individualMedicalConditionKeys(): array
    {
        return array_keys(CoverageFormTranslations::INDIVIDUAL_MEDICAL_CONDITIONS);
    }

    public static function usesIndividualPlanForm(?Plan $plan): bool
    {
        return $plan !== null && $plan->coverageFormVariant() === 'individual_plan';
    }

    public static function usesVipIndividualForm(?Plan $plan): bool
    {
        return $plan !== null && $plan->coverageFormVariant() === 'vip_individual';
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

        if (self::usesVip10DayForm($plan)) {
            return self::missingFieldLabels($membership, $primary) === [];
        }

        if (self::usesIndividualPlanForm($plan)) {
            return self::missingFieldLabels($membership, $primary) === [];
        }

        if (self::usesVipIndividualForm($plan)) {
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

        if (! self::usesFamilyForm($plan) && ! self::usesVipIndividualForm($plan) && ! self::usesVip10DayForm($plan) && ! self::usesIndividualPlanForm($plan)) {
            return array_values(array_map(
                fn (string $key) => CoverageFormTranslations::t($key),
                self::missingFieldKeys($primary, self::INDIVIDUAL_FIELDS),
            ));
        }

        if (self::usesVip10DayForm($plan)) {
            $primaryFields = self::VIP_10_DAY_PRIMARY_FIELDS;
        } elseif (self::usesVipIndividualForm($plan)) {
            $primaryFields = self::VIP_PRIMARY_FIELDS;
        } elseif (self::usesIndividualPlanForm($plan)) {
            $primaryFields = self::INDIVIDUAL_PLAN_PRIMARY_FIELDS;
        } else {
            $primaryFields = self::FAMILY_PRIMARY_FIELDS;
        }

        $missing = [];
        foreach (self::missingFieldKeys($primary, $primaryFields) as $key) {
            $missing[] = CoverageFormTranslations::t($key);
        }

        $profile = $membership->coverageProfile;
        if (! $profile) {
            if (self::usesVip10DayForm($plan)) {
                $missing[] = CoverageFormTranslations::t('mailing_address');
                $missing[] = CoverageFormTranslations::t('emergency_contact');
                $missing[] = CoverageFormTranslations::t('trip_details_section');
                $missing[] = CoverageFormTranslations::t('passport_section');
                $missing[] = CoverageFormTranslations::t('medical_section');
                $missing[] = CoverageFormTranslations::t('terms_section');
                $missing[] = CoverageFormTranslations::t('signature_section');
            } elseif (self::usesIndividualPlanForm($plan)) {
                $missing[] = CoverageFormTranslations::t('emergency_contact');
                $missing[] = CoverageFormTranslations::t('insurance_section');
                $missing[] = CoverageFormTranslations::t('health_questionnaire_section');
                $missing[] = CoverageFormTranslations::t('primary_care_provider');
                $missing[] = CoverageFormTranslations::t('medical_section');
                $missing[] = CoverageFormTranslations::t('terms_section');
            } else {
                $missing[] = CoverageFormTranslations::t('emergency_contact');
                $missing[] = CoverageFormTranslations::t('insurance_section');
                $missing[] = CoverageFormTranslations::t('health_questionnaire_section');
                $missing[] = CoverageFormTranslations::t('medical_section');
                $missing[] = CoverageFormTranslations::t('photo_id_document');
                $missing[] = CoverageFormTranslations::t('passport_document');
                $missing[] = CoverageFormTranslations::t('terms_section');

                if (self::usesVipIndividualForm($plan)) {
                    $missing[] = CoverageFormTranslations::t('physical_metrics');
                    $missing[] = CoverageFormTranslations::t('occupation');
                    $missing[] = CoverageFormTranslations::t('resident_status');
                }
            }
        } else {
            if (self::usesVip10DayForm($plan)) {
                if (blank($profile->mailing_street) || blank($profile->mailing_city) || blank($profile->mailing_state) || blank($profile->mailing_zip_code) || blank($profile->mailing_country)) {
                    $missing[] = CoverageFormTranslations::t('mailing_address');
                }
                if (blank($profile->emergency_contact_first_name) || blank($profile->emergency_contact_last_name) || blank($profile->emergency_contact_phone)) {
                    $missing[] = CoverageFormTranslations::t('emergency_contact');
                }
                if (! self::tripDetailsAreComplete($profile->trip_details)) {
                    $missing[] = CoverageFormTranslations::t('trip_details_section');
                }
                if (blank($profile->passport_issued_by)) {
                    $missing[] = CoverageFormTranslations::t('passport_section');
                }
                if (blank($profile->allergies) && blank($profile->chronic_conditions)) {
                    $missing[] = CoverageFormTranslations::t('medical_section');
                }
                if ($profile->terms_accepted_at === null) {
                    $missing[] = CoverageFormTranslations::t('terms_section');
                }
                if (blank($profile->applicant_signature) || $profile->signature_date === null) {
                    $missing[] = CoverageFormTranslations::t('signature_section');
                }
            } elseif (self::usesIndividualPlanForm($plan)) {
                if (blank($profile->emergency_contact_first_name) || blank($profile->emergency_contact_last_name) || blank($profile->emergency_contact_phone) || blank($profile->emergency_contact_relationship)) {
                    $missing[] = CoverageFormTranslations::t('emergency_contact');
                }
                if (blank($profile->health_plan_provider) || blank($profile->health_insurer)) {
                    $missing[] = CoverageFormTranslations::t('insurance_section');
                }
                if (blank($profile->primary_care_provider)) {
                    $missing[] = CoverageFormTranslations::t('primary_care_provider');
                }
                if (! self::individualHealthQuestionnaireIsComplete($profile->health_questionnaire)) {
                    $missing[] = CoverageFormTranslations::t('health_questionnaire_section');
                }
                if (blank($profile->allergies) && blank($profile->chronic_conditions) && blank($profile->other_medical_info) && empty($profile->medical_condition_flags)) {
                    $missing[] = CoverageFormTranslations::t('medical_section');
                }
                if ($profile->terms_accepted_at === null) {
                    $missing[] = CoverageFormTranslations::t('terms_section');
                }
            } else {
                if (blank($profile->emergency_contact_first_name) || blank($profile->emergency_contact_last_name) || blank($profile->emergency_contact_phone)) {
                    $missing[] = CoverageFormTranslations::t('emergency_contact');
                }
                if (blank($profile->insurance_company) || blank($profile->insurance_policy_number) || blank($profile->insurance_provider_phone)) {
                    $missing[] = CoverageFormTranslations::t('insurance_section');
                }
                if (self::usesVipIndividualForm($plan)) {
                    if (blank($profile->resident_status)) {
                        $missing[] = CoverageFormTranslations::t('resident_status');
                    }
                    if (blank($profile->occupation) || blank($profile->measurement_unit) || blank($profile->height) || blank($profile->weight)) {
                        $missing[] = CoverageFormTranslations::t('physical_metrics');
                    }
                    if (! self::healthQuestionnaireIsComplete($profile->health_questionnaire)) {
                        $missing[] = CoverageFormTranslations::t('health_questionnaire_section');
                    }
                }
                if (blank($profile->blood_type) || blank($profile->allergies)) {
                    $missing[] = CoverageFormTranslations::t('medical_section');
                }
                if (blank($profile->photo_id_path)) {
                    $missing[] = CoverageFormTranslations::t('photo_id_document');
                }
                if (blank($profile->passport_path)) {
                    $missing[] = CoverageFormTranslations::t('passport_document');
                }
                if ($profile->terms_accepted_at === null) {
                    $missing[] = CoverageFormTranslations::t('terms_section');
                }
            }
        }

        if (self::usesFamilyForm($plan)) {
            $completeDependents = collect(self::householdDependents($membership))
                ->filter(fn (MemberDependent $dep) => self::dependentIsComplete($dep))
                ->count();

            if ($completeDependents < 1) {
                $missing[] = CoverageFormTranslations::t('at_least_one_dependent');
            }
        }

        return array_values(array_unique($missing));
    }

    /**
     * @param  array<string, mixed>|null  $tripDetails
     */
    public static function tripDetailsAreComplete(?array $tripDetails): bool
    {
        if (! is_array($tripDetails)) {
            return false;
        }

        $trips = $tripDetails['trips'] ?? null;
        if (! is_array($trips) || $trips === []) {
            return false;
        }

        foreach ($trips as $trip) {
            if (! is_array($trip)) {
                return false;
            }

            if (blank($trip['from'] ?? null) || blank($trip['date'] ?? null)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  array<string, mixed>|null  $answers
     */
    public static function healthQuestionnaireIsComplete(?array $answers): bool
    {
        if (! is_array($answers)) {
            return false;
        }

        foreach (self::healthQuestionnaireKeys() as $key) {
            $value = $answers[$key] ?? null;
            if (! in_array($value, ['yes', 'no'], true)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  array<string, mixed>|null  $answers
     */
    public static function individualHealthQuestionnaireIsComplete(?array $answers): bool
    {
        if (! is_array($answers)) {
            return false;
        }

        foreach (self::individualHealthQuestionnaireKeys() as $key) {
            $value = $answers[$key] ?? null;
            if (! is_string($value) || trim($value) === '') {
                return false;
            }
        }

        return true;
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
        if ($field === 'date_of_birth' || $field === 'passport_expiry_date') {
            return $primary->{$field} !== null;
        }

        $value = $primary->{$field} ?? null;

        return is_string($value) && trim($value) !== '';
    }
}
