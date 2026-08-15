<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Member;
use App\Models\Membership;
use App\Models\Plan;
use App\Models\User;
use App\Support\MembershipNumberGenerator;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MembershipCsvImportService
{
    /** @var list<string> */
    public const RECORD_TYPES = ['b2c', 'b2b_company', 'b2b_employee'];

    /** @var list<string> */
    public const REQUIRED_COLUMNS = [
        'record_type',
        'first_name',
        'last_name',
        'plan_code',
    ];

    /** @var list<string> */
    public const OPTIONAL_COLUMNS = [
        'email',
        'membership_number',
        'status',
        'coverage_start',
        'coverage_end',
        'phone',
        'billing_provider',
        'billing_subscription_id',
        'auto_renew',
        'company_name',
        'company_billing_email',
        'company_phone',
        'company_city',
        'company_country',
        'date_of_birth',
    ];

    /** @var list<string> */
    public const ALL_COLUMNS = [
        ...self::REQUIRED_COLUMNS,
        ...self::OPTIONAL_COLUMNS,
    ];

    /** @var list<string> */
    private const ALLOWED_STATUSES = ['active', 'inactive', 'expired', 'cancelled'];

    public function __construct(
        private readonly MembershipNumberGenerator $membershipNumberGenerator = new MembershipNumberGenerator(),
        private readonly CompanyBillingService $billingService = new CompanyBillingService(),
    ) {}

    /**
     * @return array{
     *     headers: list<string>,
     *     rows: list<array<string, mixed>>,
     *     errors: list<string>,
     * }
     */
    public function parseUploadedCsv(string $path): array
    {
        $handle = fopen($path, 'r');
        if ($handle === false) {
            return [
                'headers' => [],
                'rows' => [],
                'errors' => ['Could not read the CSV file.'],
            ];
        }

        $headerRow = fgetcsv($handle);
        if ($headerRow === false) {
            fclose($handle);

            return [
                'headers' => [],
                'rows' => [],
                'errors' => ['The CSV file is empty.'],
            ];
        }

        $headers = $this->normalizeHeaders($headerRow);
        $errors = $this->validateHeaders($headers);
        if ($errors !== []) {
            fclose($handle);

            return [
                'headers' => $headers,
                'rows' => [],
                'errors' => $errors,
            ];
        }

        $rows = [];
        $line = 1;
        while (($csvRow = fgetcsv($handle)) !== false) {
            $line++;
            if ($this->isBlankRow($csvRow)) {
                continue;
            }

            $rows[] = $this->mapRow($headers, $csvRow, $line);
        }

        fclose($handle);

        return [
            'headers' => $headers,
            'rows' => $rows,
            'errors' => [],
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @return array{
     *     rows: list<array<string, mixed>>,
     *     summary: array{valid:int,warn:int,error:int},
     * }
     */
    public function analyzeRows(array $rows, bool $updateExisting = false): array
    {
        $plansByCode = Plan::query()->get()->keyBy(fn (Plan $plan) => strtoupper((string) $plan->code));
        $existingMembershipNumbers = Membership::query()->pluck('id', 'membership_number');
        $existingSubscriptionIds = Membership::query()
            ->whereNotNull('billing_subscription_id')
            ->pluck('id', 'billing_subscription_id');
        $existingEmails = User::query()->pluck('id', 'email');
        $existingCompanies = Company::query()->pluck('id', 'name');

        $emailLines = [];
        $membershipNumberLines = [];
        $subscriptionLines = [];
        $companyNamesInFile = [];

        foreach ($rows as $row) {
            $line = (int) $row['line'];
            $recordType = $this->normalizeRecordType($row['record_type'] ?? null);

            $email = strtolower(trim((string) ($row['email'] ?? '')));
            if ($email !== '' && in_array($recordType, ['b2c', 'b2b_company'], true)) {
                $emailLines[$email][] = $line;
            }

            $membershipNumber = $this->membershipNumberGenerator->normalizeProvided($row['membership_number'] ?? null);
            if ($membershipNumber !== null) {
                $membershipNumberLines[$membershipNumber][] = $line;
            }

            $subscriptionId = trim((string) ($row['billing_subscription_id'] ?? ''));
            if ($subscriptionId !== '') {
                $subscriptionLines[$subscriptionId][] = $line;
            }

            if ($recordType === 'b2b_company') {
                $companyName = trim((string) ($row['company_name'] ?? ''));
                if ($companyName !== '') {
                    $companyNamesInFile[$companyName][] = $line;
                }
            }
        }

        $analyzed = [];
        $summary = ['valid' => 0, 'warn' => 0, 'error' => 0];

        foreach ($rows as $row) {
            $issues = [];
            $level = 'valid';
            $recordType = $this->normalizeRecordType($row['record_type'] ?? null);

            $email = strtolower(trim((string) ($row['email'] ?? '')));
            if (in_array($recordType, ['b2c', 'b2b_company'], true)) {
                if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $issues[] = 'Valid email is required for '.$recordType.'.';
                    $level = 'error';
                } elseif (count($emailLines[$email] ?? []) > 1) {
                    $others = array_values(array_filter($emailLines[$email], fn ($l) => $l !== $row['line']));
                    $issues[] = 'Duplicate email in file (also on line '.implode(', ', $others).').';
                    $level = 'error';
                } elseif (isset($existingEmails[$email])) {
                    $issues[] = 'Email already exists — will link to existing portal account.';
                    if ($level !== 'error') {
                        $level = 'warn';
                    }
                }
            }

            $firstName = trim((string) ($row['first_name'] ?? ''));
            $lastName = trim((string) ($row['last_name'] ?? ''));
            if ($firstName === '') {
                $issues[] = 'first_name is required.';
                $level = 'error';
            }
            if ($lastName === '') {
                $issues[] = 'last_name is required.';
                $level = 'error';
            }

            if (! in_array($recordType, self::RECORD_TYPES, true)) {
                $issues[] = 'record_type must be b2c, b2b_company, or b2b_employee.';
                $level = 'error';
            }

            $planCode = strtoupper(trim((string) ($row['plan_code'] ?? '')));
            $plan = $plansByCode->get($planCode);
            if ($planCode === '' || ! $plan) {
                $issues[] = 'plan_code must match an active portal plan.';
                $level = 'error';
            } elseif ($plan instanceof Plan) {
                if ($recordType === 'b2c' && $plan->isBusinessCategory()) {
                    $issues[] = 'Retail (b2c) rows must use a retail plan, not a business/corporate plan.';
                    $level = 'error';
                }
                if (in_array($recordType, ['b2b_company', 'b2b_employee'], true) && ! $plan->isBusinessCategory()) {
                    $issues[] = 'Business rows must use a business or corporate plan (e.g. SMB_TEAM, ENTERPRISE).';
                    $level = 'error';
                }
            }

            $companyName = trim((string) ($row['company_name'] ?? ''));
            if (in_array($recordType, ['b2b_company', 'b2b_employee'], true) && $companyName === '') {
                $issues[] = 'company_name is required for business rows.';
                $level = 'error';
            }

            if ($recordType === 'b2b_company' && $companyName !== '' && count($companyNamesInFile[$companyName] ?? []) > 1) {
                $others = array_values(array_filter($companyNamesInFile[$companyName], fn ($l) => $l !== $row['line']));
                $issues[] = 'Duplicate company_name in file (also on line '.implode(', ', $others).').';
                $level = 'error';
            }

            if ($recordType === 'b2b_employee' && $companyName !== '') {
                if (! isset($existingCompanies[$companyName]) && ! isset($companyNamesInFile[$companyName])) {
                    $issues[] = 'Company not found — add a b2b_company row for this company_name first, or create the company in admin.';
                    $level = 'error';
                }
            }

            if ($recordType === 'b2b_company') {
                $issues[] = 'Will create or update company record and assign HR portal owner.';
                if ($level !== 'error') {
                    $level = 'warn';
                }
            }

            $membershipNumber = $this->membershipNumberGenerator->normalizeProvided($row['membership_number'] ?? null);
            if ($recordType !== 'b2b_company') {
                if ($membershipNumber !== null) {
                    if (count($membershipNumberLines[$membershipNumber] ?? []) > 1) {
                        $others = array_values(array_filter($membershipNumberLines[$membershipNumber], fn ($l) => $l !== $row['line']));
                        $issues[] = 'Duplicate membership_number in file (also on line '.implode(', ', $others).').';
                        $level = 'error';
                    } elseif (isset($existingMembershipNumbers[$membershipNumber])) {
                        if ($updateExisting) {
                            $issues[] = 'membership_number exists — row will update that membership.';
                            if ($level !== 'error') {
                                $level = 'warn';
                            }
                        } else {
                            $issues[] = 'membership_number already exists in portal (enable update to modify).';
                            $level = 'error';
                        }
                    }
                } else {
                    $issues[] = 'membership_number blank — will auto-generate HERO-IMP-'.date('Y').'-XXXXXX.';
                    if ($level !== 'error') {
                        $level = 'warn';
                    }
                }
            }

            $subscriptionId = trim((string) ($row['billing_subscription_id'] ?? ''));
            if ($subscriptionId !== '' && $recordType === 'b2c') {
                if (count($subscriptionLines[$subscriptionId] ?? []) > 1) {
                    $others = array_values(array_filter($subscriptionLines[$subscriptionId], fn ($l) => $l !== $row['line']));
                    $issues[] = 'Duplicate billing_subscription_id in file (also on line '.implode(', ', $others).').';
                    $level = 'error';
                } elseif (isset($existingSubscriptionIds[$subscriptionId])) {
                    $issues[] = 'billing_subscription_id already synced from gateway — row will be skipped.';
                    if ($level !== 'error') {
                        $level = 'warn';
                    }
                }
            }

            $status = $this->normalizeStatus($row['status'] ?? null);
            if ($status === null && trim((string) ($row['status'] ?? '')) !== '') {
                $issues[] = 'status must be active, inactive, expired, or cancelled.';
                $level = 'error';
            }

            if ($this->safeDate($row['coverage_start'] ?? null) === null && trim((string) ($row['coverage_start'] ?? '')) !== '') {
                $issues[] = 'coverage_start must be YYYY-MM-DD.';
                $level = 'error';
            }
            if ($this->safeDate($row['coverage_end'] ?? null) === null && trim((string) ($row['coverage_end'] ?? '')) !== '') {
                $issues[] = 'coverage_end must be YYYY-MM-DD.';
                $level = 'error';
            }

            $analyzed[] = array_merge($row, [
                'record_type' => $recordType,
                'plan_code' => $planCode,
                'company_name' => $companyName,
                'resolved_status' => $status ?? 'active',
                'resolved_membership_number' => $membershipNumber,
                'issues' => $issues,
                'level' => $level,
            ]);

            $summary[$level === 'valid' ? 'valid' : ($level === 'warn' ? 'warn' : 'error')]++;
        }

        return [
            'rows' => $analyzed,
            'summary' => $summary,
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @return array{
     *     created_users:int,
     *     updated_users:int,
     *     created_companies:int,
     *     updated_companies:int,
     *     created_memberships:int,
     *     updated_memberships:int,
     *     created_members:int,
     *     updated_members:int,
     *     skipped:int,
     *     errors:int,
     *     messages:list<string>,
     * }
     */
    public function importRows(array $rows, bool $updateExisting = false, bool $dryRun = false): array
    {
        $analysis = $this->analyzeRows($rows, $updateExisting);
        $result = [
            'created_users' => 0,
            'updated_users' => 0,
            'created_companies' => 0,
            'updated_companies' => 0,
            'created_memberships' => 0,
            'updated_memberships' => 0,
            'created_members' => 0,
            'updated_members' => 0,
            'skipped' => 0,
            'errors' => 0,
            'messages' => [],
        ];

        $importable = array_filter(
            $analysis['rows'],
            fn (array $row) => $row['level'] !== 'error'
        );

        if ($dryRun) {
            $result['messages'][] = 'Dry run only — no database changes were made.';
            $result['skipped'] = count($analysis['rows']) - count($importable);
            $result['errors'] = $analysis['summary']['error'];

            return $result;
        }

        $plansByCode = Plan::query()->pluck('id', 'code');
        $generator = new MembershipNumberGenerator();
        $companiesTouched = [];

        usort($importable, function (array $a, array $b) {
            $order = ['b2b_company' => 0, 'b2b_employee' => 1, 'b2c' => 2];

            return ($order[$a['record_type']] ?? 99) <=> ($order[$b['record_type']] ?? 99);
        });

        foreach ($importable as $row) {
            $line = (int) $row['line'];
            $recordType = (string) $row['record_type'];

            if ($recordType === 'b2c') {
                $subscriptionId = trim((string) ($row['billing_subscription_id'] ?? ''));
                if ($subscriptionId !== '' && Membership::query()->where('billing_subscription_id', $subscriptionId)->exists()) {
                    $result['skipped']++;
                    $result['messages'][] = "Line {$line}: skipped — gateway subscription already imported.";

                    continue;
                }
            }

            try {
                DB::transaction(function () use ($row, $plansByCode, $updateExisting, $generator, &$result, $line, $recordType, &$companiesTouched) {
                    $planId = $plansByCode[strtoupper((string) $row['plan_code'])];

                    if ($recordType === 'b2b_company') {
                        $company = $this->importBusinessCompany($row, (int) $planId, $result);
                        $companiesTouched[$company->id] = $company;
                        $result['messages'][] = "Line {$line}: company \"{$company->name}\" ready for employee imports.";

                        return;
                    }

                    if ($recordType === 'b2b_employee') {
                        $company = $this->resolveCompanyForRow($row);
                        $membershipNumber = $this->membershipNumberGenerator->normalizeProvided($row['membership_number'] ?? null)
                            ?? $generator->nextImportNumber();
                        $membership = $this->upsertEmployeeMembership(
                            $row,
                            $company,
                            (int) $planId,
                            $membershipNumber,
                            $updateExisting,
                            $result
                        );
                        $companiesTouched[$company->id] = $company;
                        $result['messages'][] = "Line {$line}: employee {$membership->membership_number} added to {$company->name}.";

                        return;
                    }

                    $this->importB2cRow($row, (int) $planId, $generator, $updateExisting, $result, $line);
                });
            } catch (\Throwable $e) {
                $result['errors']++;
                $result['messages'][] = "Line {$line}: ".$e->getMessage();
            }
        }

        foreach ($companiesTouched as $company) {
            $this->billingService->recalculate($company);
        }

        $result['errors'] += $analysis['summary']['error'];
        $result['skipped'] += count(array_filter(
            $analysis['rows'],
            fn (array $row) => $row['level'] === 'error'
        ));

        return $result;
    }

    public function sampleCsvContents(): string
    {
        $headers = implode(',', self::ALL_COLUMNS);
        $b2c = implode(',', [
            'b2c',
            'member@example.com',
            'Jane',
            'Doe',
            'HR-02',
            '',
            'active',
            date('Y-m-d'),
            date('Y-m-d', strtotime('+1 year')),
            '+1 555 010 0000',
            'manual',
            '',
            'yes',
            '',
            '',
            '',
            '',
            '',
        ]);
        $company = implode(',', [
            'b2b_company',
            'hr@acme.test',
            'Alice',
            'Owner',
            'SMB_TEAM',
            '',
            'active',
            '',
            '',
            '+1 555 010 1000',
            'manual',
            '',
            'yes',
            'Acme Logistics',
            'billing@acme.test',
            '+1 555 010 1000',
            'Miami',
            'US',
            '',
        ]);
        $employee = implode(',', [
            'b2b_employee',
            '',
            'Bob',
            'Worker',
            'SMB_TEAM',
            '',
            'active',
            date('Y-m-d'),
            date('Y-m-d', strtotime('+1 year')),
            '',
            'manual',
            '',
            'yes',
            'Acme Logistics',
            '',
            '',
            '',
            '',
            '1990-01-15',
        ]);

        return $headers."\n".$b2c."\n".$company."\n".$employee."\n";
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function importB2cRow(array $row, int $planId, MembershipNumberGenerator $generator, bool $updateExisting, array &$result, int $line): void
    {
        $email = strtolower(trim((string) $row['email']));
        $firstName = trim((string) $row['first_name']);
        $lastName = trim((string) $row['last_name']);
        $displayName = trim($firstName.' '.$lastName);
        $subscriptionId = trim((string) ($row['billing_subscription_id'] ?? ''));

        $user = User::query()->where('email', $email)->first();
        if (! $user) {
            $user = User::create([
                'name' => $displayName,
                'email' => $email,
                'password' => Str::password(24),
                'email_verified_at' => now(),
            ]);
            $user->assignRole('customer');
            $result['created_users']++;
        } else {
            if ($user->name !== $displayName && $displayName !== '') {
                $user->name = $displayName;
                $user->save();
                $result['updated_users']++;
            }
            if (! $user->hasRole('customer')) {
                $user->assignRole('customer');
            }
        }

        $membershipNumber = $this->membershipNumberGenerator->normalizeProvided($row['membership_number'] ?? null)
            ?? $generator->nextImportNumber();

        $membership = Membership::query()->where('membership_number', $membershipNumber)->first();
        if ($membership && ! $updateExisting) {
            throw new \RuntimeException('membership_number already exists.');
        }

        $coverageStart = $this->safeDate($row['coverage_start'] ?? null) ?? now()->toDateString();
        $coverageEnd = $this->safeDate($row['coverage_end'] ?? null);
        $status = $this->normalizeStatus($row['status'] ?? null) ?? 'active';
        if ($coverageEnd && Carbon::parse($coverageEnd)->isPast() && $status === 'active') {
            $status = 'expired';
        }

        $payload = [
            'plan_id' => $planId,
            'account_user_id' => $user->id,
            'company_id' => null,
            'partner_id' => null,
            'coverage_starts_on' => $coverageStart,
            'coverage_ends_on' => $coverageEnd,
            'auto_renew' => $this->normalizeBool($row['auto_renew'] ?? null, true),
            'status' => $status,
            'billing_provider' => $this->normalizeBillingProvider($row['billing_provider'] ?? null),
            'billing_subscription_id' => $subscriptionId !== '' ? $subscriptionId : null,
        ];

        if (! $membership) {
            $membership = Membership::create(array_merge($payload, [
                'membership_number' => $membershipNumber,
            ]));
            $result['created_memberships']++;
        } else {
            $membership->fill($payload);
            if ($membership->isDirty()) {
                $membership->save();
                $result['updated_memberships']++;
            }
        }

        $primary = Member::query()
            ->where('membership_id', $membership->id)
            ->where('is_primary', true)
            ->first();

        $memberPayload = [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'phone' => trim((string) ($row['phone'] ?? '')) ?: null,
            'date_of_birth' => $this->safeDate($row['date_of_birth'] ?? null),
        ];

        if (! $primary) {
            Member::create(array_merge($memberPayload, [
                'membership_id' => $membership->id,
                'is_primary' => true,
                'qr_token' => (string) Str::uuid(),
            ]));
            $result['created_members']++;
        } else {
            $primary->fill($memberPayload);
            if (! $primary->qr_token) {
                $primary->qr_token = (string) Str::uuid();
            }
            if ($primary->isDirty()) {
                $primary->save();
                $result['updated_members']++;
            }
        }

        $result['messages'][] = "Line {$line}: imported B2C {$membership->membership_number} for {$email}.";
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function importBusinessCompany(array $row, int $planId, array &$result): Company
    {
        $companyName = trim((string) $row['company_name']);
        $email = strtolower(trim((string) $row['email']));
        $firstName = trim((string) $row['first_name']);
        $lastName = trim((string) $row['last_name']);
        $displayName = trim($firstName.' '.$lastName);

        $company = Company::query()->where('name', $companyName)->first();
        $isNew = ! $company;

        if (! $company) {
            $company = Company::create([
                'name' => $companyName,
                'billing_email' => trim((string) ($row['company_billing_email'] ?? '')) ?: $email,
                'phone' => trim((string) ($row['company_phone'] ?? '')) ?: trim((string) ($row['phone'] ?? '')) ?: null,
                'city' => trim((string) ($row['company_city'] ?? '')) ?: null,
                'country' => trim((string) ($row['company_country'] ?? '')) ?: null,
                'default_plan_id' => $planId,
            ]);
            $result['created_companies']++;
        } else {
            $company->fill([
                'billing_email' => trim((string) ($row['company_billing_email'] ?? '')) ?: $company->billing_email ?: $email,
                'phone' => trim((string) ($row['company_phone'] ?? '')) ?: $company->phone,
                'city' => trim((string) ($row['company_city'] ?? '')) ?: $company->city,
                'country' => trim((string) ($row['company_country'] ?? '')) ?: $company->country,
                'default_plan_id' => $planId,
            ]);
            if ($company->isDirty()) {
                $company->save();
                $result['updated_companies']++;
            }
        }

        $user = User::query()->where('email', $email)->first();
        if (! $user) {
            $user = User::create([
                'name' => $displayName,
                'email' => $email,
                'password' => Str::password(24),
                'email_verified_at' => now(),
            ]);
            $result['created_users']++;
        } elseif ($user->name !== $displayName && $displayName !== '') {
            $user->name = $displayName;
            $user->save();
            $result['updated_users']++;
        }

        if (! $user->hasRole('business')) {
            $user->assignRole('business');
        }

        if (! $company->owner_user_id) {
            $company->owner_user_id = $user->id;
            $company->save();
            if (! $isNew && ! isset($result['updated_companies'])) {
                $result['updated_companies']++;
            }
        }

        return $company->fresh();
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function resolveCompanyForRow(array $row): Company
    {
        $companyName = trim((string) $row['company_name']);
        $company = Company::query()->where('name', $companyName)->first();
        if (! $company) {
            throw new \RuntimeException("Company \"{$companyName}\" was not found.");
        }

        return $company;
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function upsertEmployeeMembership(
        array $row,
        Company $company,
        int $planId,
        string $membershipNumber,
        bool $updateExisting,
        array &$result,
    ): Membership {
        $firstName = trim((string) $row['first_name']);
        $lastName = trim((string) $row['last_name']);
        $email = strtolower(trim((string) ($row['email'] ?? '')));

        $membership = Membership::query()->where('membership_number', $membershipNumber)->first();
        if ($membership && ! $updateExisting) {
            throw new \RuntimeException('membership_number already exists.');
        }

        $coverageStart = $this->safeDate($row['coverage_start'] ?? null) ?? now()->toDateString();
        $coverageEnd = $this->safeDate($row['coverage_end'] ?? null) ?? now()->addYear()->toDateString();
        $status = $this->normalizeStatus($row['status'] ?? null) ?? 'active';

        $payload = [
            'plan_id' => $planId,
            'account_user_id' => null,
            'company_id' => $company->id,
            'partner_id' => null,
            'coverage_starts_on' => $coverageStart,
            'coverage_ends_on' => $coverageEnd,
            'auto_renew' => $this->normalizeBool($row['auto_renew'] ?? null, true),
            'status' => $status,
            'billing_provider' => 'manual',
            'billing_subscription_id' => null,
        ];

        if (! $membership) {
            $membership = Membership::create(array_merge($payload, [
                'membership_number' => $membershipNumber,
            ]));
            $result['created_memberships']++;
        } else {
            $membership->fill($payload);
            if ($membership->isDirty()) {
                $membership->save();
                $result['updated_memberships']++;
            }
        }

        $primary = Member::query()
            ->where('membership_id', $membership->id)
            ->where('is_primary', true)
            ->first();

        $memberPayload = [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email !== '' ? $email : null,
            'phone' => trim((string) ($row['phone'] ?? '')) ?: null,
            'date_of_birth' => $this->safeDate($row['date_of_birth'] ?? null),
        ];

        if (! $primary) {
            Member::create(array_merge($memberPayload, [
                'membership_id' => $membership->id,
                'is_primary' => true,
                'qr_token' => (string) Str::uuid(),
            ]));
            $result['created_members']++;
        } else {
            $primary->fill($memberPayload);
            if (! $primary->qr_token) {
                $primary->qr_token = (string) Str::uuid();
            }
            if ($primary->isDirty()) {
                $primary->save();
                $result['updated_members']++;
            }
        }

        return $membership;
    }

    private function normalizeRecordType(?string $value): string
    {
        $type = strtolower(trim((string) $value));

        return $type === '' ? 'b2c' : $type;
    }

    /**
     * @param  list<string>  $headerRow
     * @return list<string>
     */
    private function normalizeHeaders(array $headerRow): array
    {
        return array_map(
            fn ($header) => strtolower(trim((string) $header)),
            $headerRow
        );
    }

    /**
     * @param  list<string>  $headers
     * @return list<string>
     */
    private function validateHeaders(array $headers): array
    {
        $errors = [];
        foreach (self::REQUIRED_COLUMNS as $required) {
            if (! in_array($required, $headers, true)) {
                $errors[] = 'Missing required column: '.$required;
            }
        }

        return $errors;
    }

    /**
     * @param  list<string>  $headers
     * @param  list<string|null>  $csvRow
     * @return array<string, mixed>
     */
    private function mapRow(array $headers, array $csvRow, int $line): array
    {
        $row = ['line' => $line];
        foreach ($headers as $index => $header) {
            if ($header === '') {
                continue;
            }
            $row[$header] = trim((string) ($csvRow[$index] ?? ''));
        }

        return $row;
    }

    /**
     * @param  list<string|null>  $csvRow
     */
    private function isBlankRow(array $csvRow): bool
    {
        foreach ($csvRow as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    private function normalizeStatus(?string $value): ?string
    {
        $status = strtolower(trim((string) $value));
        if ($status === '') {
            return null;
        }

        return in_array($status, self::ALLOWED_STATUSES, true) ? $status : null;
    }

    private function normalizeBillingProvider(?string $value): string
    {
        $raw = strtolower(trim((string) $value));

        return match ($raw) {
            'usa_payments', 'usa payments', 'usapayments' => 'usa_payments',
            'stripe' => 'stripe',
            default => 'manual',
        };
    }

    private function normalizeBool(?string $value, bool $default): bool
    {
        $raw = strtolower(trim((string) $value));
        if ($raw === '') {
            return $default;
        }

        return in_array($raw, ['1', 'true', 'yes', 'y'], true);
    }

    private function safeDate(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $text = trim((string) $value);
        if ($text === '') {
            return null;
        }

        try {
            return Carbon::parse($text)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }
}
