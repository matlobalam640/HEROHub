<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Member;
use App\Models\Membership;
use App\Models\Plan;
use App\Support\MembershipNumberGenerator;
use Carbon\Carbon;
use Illuminate\Support\Str;

class CompanyEmployeeCsvImportService
{
    /** @var list<string> */
    public const REQUIRED_COLUMNS = ['first_name', 'last_name'];

    /** @var list<string> */
    public const OPTIONAL_COLUMNS = [
        'date_of_birth',
        'dob',
        'email',
        'phone',
        'plan_code',
        'plan_id',
        'membership_number',
        'status',
        'coverage_start',
        'coverage_end',
    ];

    public function __construct(
        private readonly MembershipNumberGenerator $membershipNumberGenerator = new MembershipNumberGenerator(),
        private readonly CompanyBillingService $billingService = new CompanyBillingService(),
    ) {}

    public function sampleCsvContents(): string
    {
        $headers = implode(',', ['first_name', 'last_name', 'date_of_birth', 'email', 'phone', 'plan_code']);

        return $headers."\n"
            .'John,Smith,1990-05-15,john.smith@company.test,+1 555 010 1001,HR-02'."\n";
    }

    /**
     * @return array{added:int,skipped:int,messages:list<string>}
     */
    public function importForCompany(Company $company, string $csvPath): array
    {
        $handle = fopen($csvPath, 'r');
        if ($handle === false) {
            return ['added' => 0, 'skipped' => 0, 'messages' => ['Could not read the CSV file.']];
        }

        $headerRow = fgetcsv($handle);
        if ($headerRow === false) {
            fclose($handle);

            return ['added' => 0, 'skipped' => 0, 'messages' => ['The CSV file is empty.']];
        }

        $headers = array_map(fn ($h) => strtolower(trim((string) $h)), $headerRow);
        $map = array_flip($headers);
        foreach (self::REQUIRED_COLUMNS as $required) {
            if (! isset($map[$required])) {
                fclose($handle);

                return ['added' => 0, 'skipped' => 0, 'messages' => ['Missing required column: '.$required]];
            }
        }

        $defaultPlanId = $company->default_plan_id;
        if (! $defaultPlanId) {
            fclose($handle);

            return ['added' => 0, 'skipped' => 0, 'messages' => ['Set a default enrollment plan under Company billing before importing.']];
        }

        $added = 0;
        $skipped = 0;
        $messages = [];
        $line = 1;
        $generator = new MembershipNumberGenerator();

        while (($csvRow = fgetcsv($handle)) !== false) {
            $line++;
            $first = trim((string) ($csvRow[$map['first_name']] ?? ''));
            $last = trim((string) ($csvRow[$map['last_name']] ?? ''));
            if ($first === '' || $last === '') {
                $skipped++;
                $messages[] = "Line {$line}: skipped — missing first or last name.";

                continue;
            }

            $planId = $this->resolvePlanId($map, $csvRow, (int) $defaultPlanId);
            if (! Plan::query()->whereKey($planId)->exists()) {
                $skipped++;
                $messages[] = "Line {$line}: skipped — invalid plan.";

                continue;
            }

            $membershipNumber = $this->membershipNumberGenerator->normalizeProvided(
                isset($map['membership_number']) ? trim((string) ($csvRow[$map['membership_number']] ?? '')) : null
            ) ?? $generator->nextImportNumber();

            $membership = Membership::create([
                'membership_number' => $membershipNumber,
                'plan_id' => $planId,
                'account_user_id' => null,
                'company_id' => $company->id,
                'partner_id' => null,
                'coverage_starts_on' => $this->resolveDate($map, $csvRow, 'coverage_start') ?? now()->toDateString(),
                'coverage_ends_on' => $this->resolveDate($map, $csvRow, 'coverage_end') ?? now()->addYear()->toDateString(),
                'auto_renew' => true,
                'status' => $this->normalizeStatus(isset($map['status']) ? trim((string) ($csvRow[$map['status']] ?? '')) : '') ?? 'active',
                'billing_provider' => 'manual',
            ]);

            Member::create([
                'membership_id' => $membership->id,
                'is_primary' => true,
                'first_name' => $first,
                'last_name' => $last,
                'date_of_birth' => $this->resolveDateOfBirth($map, $csvRow),
                'phone' => isset($map['phone']) ? trim((string) ($csvRow[$map['phone']] ?? '')) ?: null : null,
                'email' => isset($map['email']) ? trim((string) ($csvRow[$map['email']] ?? '')) ?: null : null,
                'qr_token' => (string) Str::uuid(),
            ]);

            $added++;
            $messages[] = "Line {$line}: added {$membershipNumber} for {$first} {$last}.";
        }

        fclose($handle);
        $this->billingService->recalculate($company);

        return compact('added', 'skipped', 'messages');
    }

    /**
     * @param  array<string, int>  $map
     * @param  list<string|null>  $csvRow
     */
    private function resolvePlanId(array $map, array $csvRow, int $defaultPlanId): int
    {
        if (isset($map['plan_id']) && ($csvRow[$map['plan_id']] ?? '') !== '') {
            return (int) $csvRow[$map['plan_id']];
        }

        if (isset($map['plan_code'])) {
            $code = strtoupper(trim((string) ($csvRow[$map['plan_code']] ?? '')));
            if ($code !== '') {
                $plan = Plan::query()->where('code', $code)->first();
                if ($plan) {
                    return (int) $plan->id;
                }
            }
        }

        return $defaultPlanId;
    }

    /**
     * @param  array<string, int>  $map
     * @param  list<string|null>  $csvRow
     */
    private function resolveDateOfBirth(array $map, array $csvRow): ?string
    {
        $raw = null;
        if (isset($map['date_of_birth'])) {
            $raw = trim((string) ($csvRow[$map['date_of_birth']] ?? ''));
        } elseif (isset($map['dob'])) {
            $raw = trim((string) ($csvRow[$map['dob']] ?? ''));
        }

        return $this->normalizeDate($raw);
    }

    /**
     * @param  array<string, int>  $map
     * @param  list<string|null>  $csvRow
     */
    private function resolveDate(array $map, array $csvRow, string $column): ?string
    {
        if (! isset($map[$column])) {
            return null;
        }

        return $this->normalizeDate(trim((string) ($csvRow[$map[$column]] ?? '')));
    }

    private function normalizeStatus(?string $value): ?string
    {
        $status = strtolower(trim((string) $value));
        if ($status === '') {
            return null;
        }

        return in_array($status, ['active', 'inactive', 'expired', 'cancelled'], true) ? $status : null;
    }

    private function normalizeDate(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $value = trim($value);
        foreach (['Y-m-d', 'm/d/Y', 'd/m/Y'] as $format) {
            try {
                $parsed = Carbon::createFromFormat($format, $value);
                if ($parsed !== false) {
                    return $parsed->toDateString();
                }
            } catch (\Throwable) {
            }
        }

        try {
            return Carbon::parse($value)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }
}
