<?php

namespace App\Console\Commands;

use App\Models\Member;
use App\Models\Membership;
use App\Models\Plan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ImportStaffMembershipsCommand extends Command
{
    protected $signature = 'import:staff-memberships
                            {json : Path to normalized JSON file}
                            {--dry-run : Validate and summarize without writing}';

    protected $description = 'Import staff users and memberships from normalized JSON export.';

    public function handle(): int
    {
        $jsonPath = (string) $this->argument('json');
        if (! is_file($jsonPath)) {
            $this->error("JSON file not found: {$jsonPath}");

            return self::FAILURE;
        }

        $raw = file_get_contents($jsonPath);
        $rows = json_decode((string) $raw, true);
        if (! is_array($rows)) {
            $this->error('Invalid JSON payload.');

            return self::FAILURE;
        }

        $plansByCode = Plan::query()->pluck('id', 'code');
        $planMap = [
            'local individual annual' => 'HR-02',
            'local individual monthly' => 'HR-02',
            'vip individual annual' => 'HR-02C',
            'vip individual monthly' => 'HR-02C',
        ];

        $createdUsers = 0;
        $updatedUsers = 0;
        $createdMemberships = 0;
        $updatedMemberships = 0;
        $createdMembers = 0;
        $updatedMembers = 0;
        $skipped = 0;
        $errors = 0;

        foreach ($rows as $index => $row) {
            $line = $index + 1;
            if (! is_array($row)) {
                $skipped++;
                continue;
            }

            $name = trim((string) ($row['name'] ?? ''));
            $membershipNumber = trim((string) ($row['membership_id'] ?? ''));
            $planLabel = strtolower(trim((string) ($row['plan'] ?? '')));

            if ($name === '' || $membershipNumber === '') {
                $this->warn("[skip line {$line}] Missing name or membership_id.");
                $skipped++;
                continue;
            }

            $planCode = $planMap[$planLabel] ?? (str_contains($planLabel, 'vip') ? 'HR-02C' : 'HR-02');
            $planId = $plansByCode[$planCode] ?? null;
            if (! $planId) {
                $this->error("[line {$line}] Plan code {$planCode} not found in plans table.");
                $errors++;
                continue;
            }

            $email = trim((string) ($row['email'] ?? ''));
            if ($email === '') {
                $slug = Str::slug($name, '.');
                if ($slug === '') {
                    $slug = 'member';
                }
                $email = strtolower($slug.'.'.strtolower($membershipNumber).'@staff-import.herohub.local');
            }

            $nameParts = preg_split('/\s+/', $name, 2, PREG_SPLIT_NO_EMPTY) ?: [];
            $firstName = $nameParts[0] ?? 'Member';
            $lastName = $nameParts[1] ?? 'Member';

            $startDate = $this->safeDate($row['registration_date'] ?? null) ?? now()->toDateString();
            $endDate = $this->safeDate($row['expiration_date'] ?? null);
            $status = $endDate && Carbon::parse($endDate)->isPast() ? 'expired' : 'active';

            if ($this->option('dry-run')) {
                continue;
            }

            try {
                $user = User::query()->where('email', $email)->first();
                if (! $user) {
                    $user = User::create([
                        'name' => $name,
                        'email' => $email,
                        'password' => Str::password(24),
                        'email_verified_at' => now(),
                    ]);
                    $user->assignRole('customer');
                    $createdUsers++;
                } else {
                    $changed = false;
                    if ($user->name !== $name && $name !== '') {
                        $user->name = $name;
                        $changed = true;
                    }
                    if ($changed) {
                        $user->save();
                        $updatedUsers++;
                    }
                    if (! $user->hasRole('customer')) {
                        $user->assignRole('customer');
                    }
                }

                $membership = Membership::query()->where('membership_number', $membershipNumber)->first();
                if (! $membership) {
                    $membership = Membership::create([
                        'membership_number' => $membershipNumber,
                        'plan_id' => $planId,
                        'account_user_id' => $user->id,
                        'company_id' => null,
                        'partner_id' => null,
                        'coverage_starts_on' => $startDate,
                        'coverage_ends_on' => $endDate,
                        'auto_renew' => true,
                        'status' => $status,
                        'billing_provider' => 'manual',
                    ]);
                    $createdMemberships++;
                } else {
                    $membership->fill([
                        'plan_id' => $planId,
                        'account_user_id' => $user->id,
                        'coverage_starts_on' => $startDate,
                        'coverage_ends_on' => $endDate,
                        'status' => $status,
                        'billing_provider' => $membership->billing_provider ?: 'manual',
                    ]);
                    if ($membership->isDirty()) {
                        $membership->save();
                        $updatedMemberships++;
                    }
                }

                $primary = Member::query()
                    ->where('membership_id', $membership->id)
                    ->where('is_primary', true)
                    ->first();

                if (! $primary) {
                    Member::create([
                        'membership_id' => $membership->id,
                        'is_primary' => true,
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'email' => $email,
                        'qr_token' => (string) Str::uuid(),
                    ]);
                    $createdMembers++;
                } else {
                    $primary->fill([
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'email' => $email,
                    ]);
                    if (! $primary->qr_token) {
                        $primary->qr_token = (string) Str::uuid();
                    }
                    if ($primary->isDirty()) {
                        $primary->save();
                        $updatedMembers++;
                    }
                }
            } catch (\Throwable $e) {
                $errors++;
                $this->error("[line {$line}] {$e->getMessage()}");
            }
        }

        $this->newLine();
        $this->line('Import summary');
        $this->line('--------------');
        $this->line('Created users: '.$createdUsers);
        $this->line('Updated users: '.$updatedUsers);
        $this->line('Created memberships: '.$createdMemberships);
        $this->line('Updated memberships: '.$updatedMemberships);
        $this->line('Created primary members: '.$createdMembers);
        $this->line('Updated primary members: '.$updatedMembers);
        $this->line('Skipped rows: '.$skipped);
        $this->line('Errors: '.$errors);

        return $errors > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function safeDate(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof Carbon) {
            return $value->toDateString();
        }

        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance($value)->toDateString();
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
