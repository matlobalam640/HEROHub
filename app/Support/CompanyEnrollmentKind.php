<?php

namespace App\Support;

final class CompanyEnrollmentKind
{
    public const CORPORATE = 'corporate';

    public const SMALL_BUSINESS = 'small_business';

    /**
     * @return list<string>
     */
    public static function tierKeys(string $kind): array
    {
        return match ($kind) {
            self::SMALL_BUSINESS => ['workplace', 'manager'],
            default => ['workplace', 'manager', 'executive'],
        };
    }

    public static function planCategory(string $kind): string
    {
        return match ($kind) {
            self::SMALL_BUSINESS => 'business',
            default => 'corporate',
        };
    }

    public static function submittedAtColumn(string $kind): string
    {
        return match ($kind) {
            self::SMALL_BUSINESS => 'small_business_submitted_at',
            default => 'submitted_at',
        };
    }
}
