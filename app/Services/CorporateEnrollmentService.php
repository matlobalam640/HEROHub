<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Member;
use App\Models\Membership;
use App\Models\Plan;
use App\Support\CompanyEnrollmentKind;
use App\Support\CorporateEnrollmentRequirement;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CorporateEnrollmentService
{
    public function __construct(
        private CompanyBillingService $billingService
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public function submit(Company $company, array $validated, string $kind = CompanyEnrollmentKind::CORPORATE): void
    {
        if (CorporateEnrollmentRequirement::isComplete($company, $kind)) {
            $this->updateProfile($company, $validated, $kind);

            return;
        }

        $created = 0;
        $tiers = CompanyEnrollmentKind::tierKeys($kind);

        foreach ($tiers as $tier) {
            $rows = CorporateEnrollmentRequirement::filterFilledRows($validated[$tier.'_enrollments'] ?? []);
            $allowedPlanIds = $this->planIdsForTier($tier, $kind);

            foreach ($rows as $row) {
                $planId = (int) ($row['plan_id'] ?? 0);
                if (! in_array($planId, $allowedPlanIds, true)) {
                    throw ValidationException::withMessages([
                        $tier.'_enrollments' => 'Selected plan is not valid for '.$tier.' coverage.',
                    ]);
                }

                $membership = Membership::create([
                    'membership_number' => 'HERO-CO-'.strtoupper(Str::random(10)),
                    'plan_id' => $planId,
                    'account_user_id' => null,
                    'company_id' => $company->id,
                    'partner_id' => null,
                    'coverage_starts_on' => now(),
                    'coverage_ends_on' => now()->addYear(),
                    'auto_renew' => true,
                    'status' => 'active',
                ]);

                Member::create([
                    'membership_id' => $membership->id,
                    'is_primary' => true,
                    'first_name' => trim((string) $row['first_name']),
                    'last_name' => trim((string) $row['last_name']),
                    'date_of_birth' => $row['date_of_birth'],
                    'qr_token' => (string) Str::uuid(),
                ]);

                $created++;
            }
        }

        if ($created < 1) {
            throw ValidationException::withMessages([
                'workplace_enrollments' => 'Add at least one employee with name, plan, and date of birth.',
            ]);
        }

        $this->updateProfile($company, $validated, $kind, markSubmitted: true);

        if ($kind === CompanyEnrollmentKind::SMALL_BUSINESS && ! blank($validated['business_name'] ?? null)) {
            $company->update(['name' => trim((string) $validated['business_name'])]);
        }

        $this->billingService->recalculate($company);
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function updateProfile(Company $company, array $validated, string $kind, bool $markSubmitted = false): void
    {
        $payload = [
            'business_name' => trim((string) ($validated['business_name'] ?? $company->name)),
            'enrollment_kind' => $kind,
            'contact_first_name' => trim($validated['contact_first_name']),
            'contact_last_name' => trim($validated['contact_last_name']),
            'contact_position' => trim((string) ($validated['contact_position'] ?? '')) ?: null,
            'contact_phone' => trim($validated['contact_phone']),
            'workplace_enrollments' => CorporateEnrollmentRequirement::filterFilledRows($validated['workplace_enrollments'] ?? []),
            'manager_enrollments' => CorporateEnrollmentRequirement::filterFilledRows($validated['manager_enrollments'] ?? []),
            'executive_enrollments' => CorporateEnrollmentRequirement::filterFilledRows($validated['executive_enrollments'] ?? []),
            'terms_accepted_at' => now(),
        ];

        if ($markSubmitted) {
            $column = CompanyEnrollmentKind::submittedAtColumn($kind);
            $payload[$column] = now();
        }

        $company->enrollmentProfile()->updateOrCreate(
            ['company_id' => $company->id],
            $payload
        );
    }

    /**
     * @return list<string>
     */
    public static function tierKeys(string $kind = CompanyEnrollmentKind::CORPORATE): array
    {
        return CompanyEnrollmentKind::tierKeys($kind);
    }

    /**
     * @return list<int>
     */
    private function planIdsForTier(string $tier, string $kind): array
    {
        return Plan::query()
            ->where('category', CompanyEnrollmentKind::planCategory($kind))
            ->where('tier', $tier)
            ->where('active', true)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }
}
