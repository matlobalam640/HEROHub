<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Member;
use App\Models\Membership;
use App\Models\Plan;
use App\Models\User;
use App\Support\MembershipNumberGenerator;
use App\Support\PortalInvite;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CompanyEmployeeCsvImportService
{
    /** @var list<string> */
    public const REQUIRED_COLUMNS = ['first_name', 'last_name', 'email'];

    /** @var list<string> */
    public const OPTIONAL_COLUMNS = [
        'date_of_birth',
        'dob',
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
        $headers = implode(',', ['first_name', 'last_name', 'email', 'date_of_birth', 'phone', 'plan_code']);

        return $headers."\n"
            .'John,Smith,john.smith@company.test,1990-05-15,+1 555 010 1001,'."\n"
            .'Jane,Doe,jane.doe@company.test,1988-11-02,+1 555 010 1002,'."\n";
    }

    /**
     * @return array{added:int,skipped:int,invited:int,messages:list<string>}
     */
    public function importForCompany(Company $company, string $csvPath): array
    {
        if (! $company->isEnrollmentActive()) {
            return [
                'added' => 0,
                'skipped' => 0,
                'invited' => 0,
                'messages' => ['Complete plan selection and USA Payments checkout before importing employees.'],
            ];
        }

        $handle = fopen($csvPath, 'r');
        if ($handle === false) {
            return ['added' => 0, 'skipped' => 0, 'invited' => 0, 'messages' => ['Could not read the CSV file.']];
        }

        $headerRow = fgetcsv($handle);
        if ($headerRow === false) {
            fclose($handle);

            return ['added' => 0, 'skipped' => 0, 'invited' => 0, 'messages' => ['The CSV file is empty.']];
        }

        $headers = array_map(fn ($h) => strtolower(trim((string) $h)), $headerRow);
        $map = array_flip($headers);
        foreach (self::REQUIRED_COLUMNS as $required) {
            if (! isset($map[$required])) {
                fclose($handle);

                return ['added' => 0, 'skipped' => 0, 'invited' => 0, 'messages' => ['Missing required column: '.$required]];
            }
        }

        $defaultPlanId = $company->default_plan_id;
        if (! $defaultPlanId) {
            fclose($handle);

            return ['added' => 0, 'skipped' => 0, 'invited' => 0, 'messages' => ['Set a default enrollment plan under Company billing before importing.']];
        }

        $rows = [];
        $line = 1;
        while (($csvRow = fgetcsv($handle)) !== false) {
            $line++;
            $first = trim((string) ($csvRow[$map['first_name']] ?? ''));
            $last = trim((string) ($csvRow[$map['last_name']] ?? ''));
            $email = strtolower(trim((string) ($csvRow[$map['email']] ?? '')));
            if ($first === '' && $last === '' && $email === '') {
                continue;
            }
            $rows[] = ['line' => $line, 'row' => $csvRow, 'first' => $first, 'last' => $last, 'email' => $email];
        }
        fclose($handle);

        $needed = count($rows);
        if ($needed === 0) {
            return ['added' => 0, 'skipped' => 0, 'invited' => 0, 'messages' => ['No employee rows found in the CSV.']];
        }

        if (! $company->canAddEmployees($needed)) {
            $remaining = $company->remainingSeats();

            return [
                'added' => 0,
                'skipped' => $needed,
                'invited' => 0,
                'messages' => [
                    'Import blocked: this file has '.$needed.' employees but only '
                    .($remaining === null ? '0' : (string) $remaining)
                    .' seat(s) remain on the company plan (limit: '.($company->seat_limit ?? '—').').',
                ],
            ];
        }

        $added = 0;
        $skipped = 0;
        $invited = 0;
        $messages = [];
        $generator = new MembershipNumberGenerator();
        $seenEmails = [];

        foreach ($rows as $item) {
            $line = $item['line'];
            $csvRow = $item['row'];
            $first = $item['first'];
            $last = $item['last'];
            $email = $item['email'];

            if ($first === '' || $last === '') {
                $skipped++;
                $messages[] = "Line {$line}: skipped — missing first or last name.";

                continue;
            }

            if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $skipped++;
                $messages[] = "Line {$line}: skipped — a valid email is required to invite the employee.";

                continue;
            }

            if (isset($seenEmails[$email])) {
                $skipped++;
                $messages[] = "Line {$line}: skipped — duplicate email in this file.";

                continue;
            }
            $seenEmails[$email] = true;

            if (! $company->canAddEmployees(1)) {
                $skipped++;
                $messages[] = "Line {$line}: skipped — company seat limit reached.";

                continue;
            }

            $planId = $this->resolvePlanId($map, $csvRow, (int) $defaultPlanId);
            if (! Plan::query()->whereKey($planId)->exists()) {
                $skipped++;
                $messages[] = "Line {$line}: skipped — invalid plan.";

                continue;
            }

            try {
                [$user, $userCreated] = $this->resolveOrCreateEmployeeUser($email, $first, $last);
            } catch (ValidationException $e) {
                $skipped++;
                $messages[] = "Line {$line}: skipped — ".collect($e->errors())->flatten()->first();

                continue;
            }

            $membershipNumber = $this->membershipNumberGenerator->normalizeProvided(
                isset($map['membership_number']) ? trim((string) ($csvRow[$map['membership_number']] ?? '')) : null
            ) ?? $generator->nextImportNumber();

            $membership = Membership::create([
                'membership_number' => $membershipNumber,
                'plan_id' => $planId,
                'account_user_id' => $user->id,
                'company_id' => $company->id,
                'partner_id' => null,
                'coverage_starts_on' => $this->resolveDate($map, $csvRow, 'coverage_start') ?? now()->toDateString(),
                'coverage_ends_on' => $this->resolveDate($map, $csvRow, 'coverage_end') ?? now()->addYear()->toDateString(),
                'auto_renew' => true,
                'status' => $this->normalizeStatus(isset($map['status']) ? trim((string) ($csvRow[$map['status']] ?? '')) : '') ?? 'active',
                'billing_provider' => 'company',
            ]);

            Member::create([
                'membership_id' => $membership->id,
                'is_primary' => true,
                'first_name' => $first,
                'last_name' => $last,
                'date_of_birth' => $this->resolveDateOfBirth($map, $csvRow),
                'phone' => isset($map['phone']) ? trim((string) ($csvRow[$map['phone']] ?? '')) ?: null : null,
                'email' => $email,
                'qr_token' => (string) Str::uuid(),
            ]);

            if ($userCreated) {
                $setupUrl = PortalInvite::passwordSetupUrl($user);
                PortalInvite::sendUserInvite(
                    user: $user,
                    subject: 'Your HERO member portal invitation',
                    headline: 'You have been enrolled by '.$company->name.'.',
                    detailLines: [
                        'Company: '.$company->name,
                        'Membership #: '.$membershipNumber,
                        'Create your password to open your member portal and view coverage.',
                    ],
                    actionUrl: $setupUrl,
                    actionLabel: 'Create your portal password',
                );
                $invited++;
            }

            $added++;
            $messages[] = "Line {$line}: added {$membershipNumber} for {$first} {$last}"
                .($userCreated ? ' (invite sent).' : ' (existing portal user linked).');
        }

        $this->billingService->recalculate($company->fresh());

        return compact('added', 'skipped', 'invited', 'messages');
    }

    /**
     * @return array{0: User, 1: bool}
     */
    public function resolveOrCreateEmployeeUser(string $email, string $firstName, string $lastName): array
    {
        $user = User::query()->where('email', $email)->first();
        if ($user) {
            if ($user->hasRole('admin')) {
                throw ValidationException::withMessages([
                    'email' => 'This email cannot be used for an employee portal account.',
                ]);
            }

            if (! $user->hasRole('customer')) {
                $user->assignRole('customer');
            }

            return [$user, false];
        }

        $user = User::create([
            'name' => trim($firstName.' '.$lastName),
            'email' => $email,
            'password' => Hash::make(Str::password(32)),
            'email_verified_at' => now(),
        ]);
        $user->assignRole('customer');

        return [$user, true];
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
