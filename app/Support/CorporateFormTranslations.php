<?php

namespace App\Support;

final class CorporateFormTranslations
{
    public static function en(string $key): string
    {
        return self::LABELS[$key]['en'] ?? $key;
    }

    /** @var array<string, array{en: string}> */
    public const LABELS = [
        'form_title' => ['en' => 'HERO Client Rescue S.A Corporate Plans'],
        'contact_information' => ['en' => 'Contact information'],
        'employee_enrollments' => ['en' => 'Employee enrollments'],
        'first_name' => ['en' => 'First Name'],
        'last_name' => ['en' => 'Last Name'],
        'position' => ['en' => 'Position'],
        'phone' => ['en' => 'Phone'],
        'name' => ['en' => 'Name'],
        'plans' => ['en' => 'Plans'],
        'date_of_birth' => ['en' => 'Date of birth'],
        'workplace_coverage' => ['en' => 'Workplace Coverage'],
        'manager_plans' => ['en' => 'Manager Plans'],
        'executive_plans' => ['en' => 'Executive Plans'],
        'add_more' => ['en' => '+ Add more'],
        'remove' => ['en' => 'Remove'],
        'select_plan' => ['en' => 'Select plan…'],
        'terms_section' => ['en' => 'Terms and conditions'],
        'terms_accept' => ['en' => 'I accept the Terms and Conditions'],
        'corporate_pricing_note' => ['en' => '*400+ Employee plans required for corporate pricing.'],
        'submit' => ['en' => 'Submit'],
        'section_help' => ['en' => 'Add employees covered under this tier. Use + to add another row.'],
        'enrollment_banner_title' => ['en' => 'Complete your corporate enrollment'],
        'enrollment_banner_body' => ['en' => 'Submit the HERO Client Rescue S.A Corporate Plans form to enroll your workforce.'],
        'enrollment_complete_now' => ['en' => 'Complete enrollment'],
        'saved_status' => ['en' => 'Corporate enrollment submitted. Employee coverage records have been created.'],
    ];
}
