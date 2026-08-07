<?php

namespace App\Support;

use App\Models\Company;
use App\Models\CompanyEnrollmentProfile;

class CorporateEnrollmentRequirement
{
    public static function profileFor(Company $company): ?CompanyEnrollmentProfile
    {
        return CompanyEnrollmentProfile::query()
            ->where('company_id', $company->id)
            ->first();
    }

    public static function isComplete(Company $company, string $kind = CompanyEnrollmentKind::CORPORATE): bool
    {
        $profile = self::profileFor($company);
        if (! $profile) {
            return false;
        }

        $column = CompanyEnrollmentKind::submittedAtColumn($kind);

        return $profile->{$column} !== null;
    }

    /**
     * @return list<string>
     */
    public static function missingSectionLabels(Company $company, string $kind = CompanyEnrollmentKind::CORPORATE): array
    {
        if (self::isComplete($company, $kind)) {
            return [];
        }

        $profile = self::profileFor($company);
        $missing = [];
        $labels = $kind === CompanyEnrollmentKind::SMALL_BUSINESS
            ? SmallBusinessFormTranslations::class
            : CorporateFormTranslations::class;

        if ($kind === CompanyEnrollmentKind::SMALL_BUSINESS && (! $profile || blank($profile->business_name))) {
            $missing[] = $labels::en('business_name');
        }

        if (! $profile || blank($profile->contact_first_name) || blank($profile->contact_last_name) || blank($profile->contact_phone)) {
            $missing[] = $labels::en('contact_information');
        }

        if (! $profile || ! self::hasAnyEnrollmentRows($profile, $kind)) {
            $missing[] = $labels::en('employee_enrollments');
        }

        if (! $profile || $profile->terms_accepted_at === null) {
            $missing[] = $labels::en('terms_section');
        }

        return array_values(array_unique($missing));
    }

    public static function hasAnyEnrollmentRows(?CompanyEnrollmentProfile $profile, string $kind = CompanyEnrollmentKind::CORPORATE): bool
    {
        if (! $profile) {
            return false;
        }

        foreach (CompanyEnrollmentKind::tierKeys($kind) as $tier) {
            $key = $tier.'_enrollments';
            $rows = $profile->{$key};
            if (! is_array($rows)) {
                continue;
            }

            foreach ($rows as $row) {
                if (self::rowIsFilled(is_array($row) ? $row : [])) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $row
     */
    public static function rowIsFilled(array $row): bool
    {
        return ! blank($row['first_name'] ?? null)
            && ! blank($row['last_name'] ?? null)
            && ! blank($row['plan_id'] ?? null)
            && ! blank($row['date_of_birth'] ?? null);
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @return list<array<string, mixed>>
     */
    public static function filterFilledRows(array $rows): array
    {
        return array_values(array_filter(
            $rows,
            fn ($row) => is_array($row) && self::rowIsFilled($row),
        ));
    }
}
