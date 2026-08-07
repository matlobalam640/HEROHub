<?php

namespace App\Support;

final class SmallBusinessFormTranslations
{
    public static function en(string $key): string
    {
        return self::LABELS[$key]['en'] ?? $key;
    }

    /** @var array<string, array{en: string}> */
    public const LABELS = [
        'form_title' => ['en' => 'Hero Client Rescue S.A Small Business Plans'],
        'business_name' => ['en' => 'Business name'],
        'contact_information' => ['en' => 'Contact information'],
        'employee_enrollments' => ['en' => 'Employee enrollments'],
        'first_name' => ['en' => 'First Name'],
        'last_name' => ['en' => 'Last Name'],
        'position' => ['en' => 'Position'],
        'phone' => ['en' => 'Phone'],
        'name' => ['en' => 'Name'],
        'plans' => ['en' => 'Plans'],
        'date_of_birth' => ['en' => 'Date of birth'],
        'onsite_workplace_coverage' => ['en' => 'On-Site Workplace Coverage'],
        'manager_plan' => ['en' => 'Manager Plan'],
        'add_more' => ['en' => '+ Add more'],
        'remove' => ['en' => 'Remove'],
        'select_plan' => ['en' => 'Select plan…'],
        'terms_section' => ['en' => 'Terms and Conditions'],
        'terms_accept' => ['en' => 'I accept the Terms and Conditions'],
        'small_business_pricing_note' => ['en' => '*25+ Employees required for small business pricing.'],
        'submit' => ['en' => 'Submit'],
        'section_help' => ['en' => 'Add employees covered under this tier. Use + to add another row.'],
        'enrollment_banner_title' => ['en' => 'Complete your small business enrollment'],
        'enrollment_banner_body' => ['en' => 'Submit the Hero Client Rescue S.A Small Business Plans form to enroll your team.'],
        'enrollment_complete_now' => ['en' => 'Complete enrollment'],
        'saved_status' => ['en' => 'Small business enrollment submitted. Employee coverage records have been created.'],
    ];
}
