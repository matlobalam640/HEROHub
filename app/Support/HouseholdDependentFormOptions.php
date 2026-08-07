<?php

namespace App\Support;

final class HouseholdDependentFormOptions
{
    /**
     * @return array<string, string>
     */
    public static function genderOptionKeys(): array
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
    public static function relationshipOptionKeys(): array
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
     * @return array<string, mixed>
     */
    public static function singleDependentValidationRules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:80'],
            'last_name' => ['required', 'string', 'max:80'],
            'date_of_birth' => ['required', 'date', 'before:today', 'after:1900-01-01'],
            'gender' => ['required', 'string', 'in:female,male,other,prefer_not_to_say'],
            'relationship' => ['required', 'string', 'max:40'],
        ];
    }

    public static function relationshipLabel(?string $value): string
    {
        if ($value === null || $value === '') {
            return 'Dependent';
        }

        $key = self::relationshipOptionKeys()[$value] ?? null;

        return $key ? CoverageFormTranslations::en($key) : ucfirst(str_replace('_', ' ', $value));
    }

    public static function genderLabel(?string $value): string
    {
        if ($value === null || $value === '') {
            return '—';
        }

        $key = self::genderOptionKeys()[$value] ?? null;

        return $key ? CoverageFormTranslations::en($key) : ucfirst($value);
    }
}
