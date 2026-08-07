<?php

namespace App\Http\Controllers\Business\Concerns;

use App\Services\CorporateEnrollmentService;
use App\Support\CompanyEnrollmentKind;
use Illuminate\Http\Request;

trait HandlesCompanyEnrollmentSubmission
{
    /**
     * @return list<array<string, mixed>>
     */
    protected function nonEmptyEnrollmentRows(mixed $rows): array
    {
        if (! is_array($rows)) {
            return [];
        }

        return array_values(array_filter($rows, function ($row) {
            if (! is_array($row)) {
                return false;
            }

            return ! blank($row['first_name'] ?? null)
                || ! blank($row['last_name'] ?? null)
                || ! blank($row['plan_id'] ?? null)
                || ! blank($row['date_of_birth'] ?? null);
        }));
    }

    protected function filterEnrollmentRequest(Request $request, string $kind): void
    {
        foreach (CorporateEnrollmentService::tierKeys($kind) as $tier) {
            $key = $tier.'_enrollments';
            $request->merge([
                $key => $this->nonEmptyEnrollmentRows($request->input($key, [])),
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function enrollmentValidationRules(string $kind, bool $requireBusinessName = false): array
    {
        $rules = [
            'contact_first_name' => ['required', 'string', 'max:80'],
            'contact_last_name' => ['required', 'string', 'max:80'],
            'contact_position' => ['nullable', 'string', 'max:120'],
            'contact_phone' => ['required', 'string', 'max:30'],
            'terms_accepted' => ['accepted'],
        ];

        if ($requireBusinessName) {
            $rules['business_name'] = ['required', 'string', 'max:160'];
        }

        foreach (CorporateEnrollmentService::tierKeys($kind) as $tier) {
            $rules[$tier.'_enrollments'] = ['nullable', 'array'];
            $rules[$tier.'_enrollments.*.first_name'] = ['required', 'string', 'max:80'];
            $rules[$tier.'_enrollments.*.last_name'] = ['required', 'string', 'max:80'];
            $rules[$tier.'_enrollments.*.plan_id'] = ['required', 'integer', 'exists:plans,id'];
            $rules[$tier.'_enrollments.*.date_of_birth'] = ['required', 'date', 'before_or_equal:today', 'after:1900-01-01'];
        }

        return $rules;
    }
}
