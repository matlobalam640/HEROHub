<?php

namespace App\Services;

use App\Models\Member;
use App\Models\Membership;
use App\Models\Plan;
use App\Models\User;
use App\Support\MembershipNumberGenerator;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class WalkInEnrollmentService
{
    /** @var list<string> */
    public const WALK_IN_PLAN_CATEGORIES = ['retail', 'business', 'corporate'];

    public function __construct(
        private readonly MembershipNumberGenerator $membershipNumberGenerator = new MembershipNumberGenerator(),
    ) {}

    /**
     * @param  array{
     *     first_name: string,
     *     last_name: string,
     *     email: string,
     *     phone?: ?string,
     *     plan_id: int,
     * }  $data
     * @return array{user: User, membership: Membership, plan: Plan}
     */
    public function createPendingRetailEnrollment(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $plan = $this->resolveWalkInPlan((int) $data['plan_id']);
            $email = strtolower(trim($data['email']));
            $user = $this->resolveOrCreateCustomer($email, trim($data['first_name']), trim($data['last_name']));

            $membership = Membership::create([
                'membership_number' => $this->membershipNumberGenerator->nextWalkInNumber(),
                'plan_id' => $plan->id,
                'account_user_id' => $user->id,
                'company_id' => null,
                'partner_id' => null,
                'coverage_starts_on' => now()->toDateString(),
                'coverage_ends_on' => null,
                'auto_renew' => false,
                'status' => 'inactive',
                'billing_provider' => 'manual',
            ]);

            Member::create([
                'membership_id' => $membership->id,
                'is_primary' => true,
                'first_name' => trim($data['first_name']),
                'last_name' => trim($data['last_name']),
                'phone' => isset($data['phone']) ? trim((string) $data['phone']) ?: null : null,
                'email' => $email,
                'qr_token' => (string) Str::uuid(),
            ]);

            return [
                'user' => $user,
                'membership' => $membership->fresh(['members', 'plan']),
                'plan' => $plan,
            ];
        });
    }

    /**
     * @param  array{
     *     first_name: string,
     *     last_name: string,
     *     email: string,
     *     phone?: ?string,
     *     plan_id: int,
     *     interval?: string,
     * }  $data
     */
    public function enrollRetailWithManualPayment(array $data, string $interval = 'yearly'): Membership
    {
        return DB::transaction(function () use ($data, $interval) {
            $plan = $this->resolveWalkInPlan((int) $data['plan_id']);
            $email = strtolower(trim($data['email']));
            $user = $this->resolveOrCreateCustomer($email, trim($data['first_name']), trim($data['last_name']));

            $starts = now()->startOfDay();
            $ends = $this->coverageEndsOn($starts, $plan, $interval);

            $membership = Membership::create([
                'membership_number' => $this->membershipNumberGenerator->nextWalkInNumber(),
                'plan_id' => $plan->id,
                'account_user_id' => $user->id,
                'company_id' => null,
                'partner_id' => null,
                'coverage_starts_on' => $starts->toDateString(),
                'coverage_ends_on' => $ends->toDateString(),
                'auto_renew' => true,
                'status' => 'active',
                'billing_provider' => 'manual',
            ]);

            Member::create([
                'membership_id' => $membership->id,
                'is_primary' => true,
                'first_name' => trim($data['first_name']),
                'last_name' => trim($data['last_name']),
                'phone' => isset($data['phone']) ? trim((string) $data['phone']) ?: null : null,
                'email' => $email,
                'qr_token' => (string) Str::uuid(),
            ]);

            return $membership->fresh(['members', 'plan']);
        });
    }

    private function resolveWalkInPlan(int $planId): Plan
    {
        $plan = Plan::query()
            ->whereKey($planId)
            ->whereIn('category', self::WALK_IN_PLAN_CATEGORIES)
            ->where('active', true)
            ->first();

        if (! $plan) {
            throw ValidationException::withMessages([
                'plan_id' => 'Select a valid active plan.',
            ]);
        }

        return $plan;
    }

    private function resolveOrCreateCustomer(string $email, string $firstName, string $lastName): User
    {
        $user = User::query()->where('email', $email)->first();

        if ($user) {
            if ($user->hasRole('admin')) {
                throw ValidationException::withMessages([
                    'email' => 'This email cannot be used for a new enrollment.',
                ]);
            }

            $hasActive = Membership::query()
                ->where('account_user_id', $user->id)
                ->where('status', 'active')
                ->exists();

            if ($hasActive) {
                throw ValidationException::withMessages([
                    'email' => 'This person already has an active membership.',
                ]);
            }

            if (! $user->hasRole('customer')) {
                $user->assignRole('customer');
            }

            return $user;
        }

        $user = User::create([
            'name' => trim($firstName.' '.$lastName),
            'email' => $email,
            'password' => Hash::make(Str::password(32)),
            'email_verified_at' => now(),
        ]);
        $user->assignRole('customer');

        return $user;
    }

    private function coverageEndsOn(CarbonInterface $start, Plan $plan, string $interval): CarbonInterface
    {
        if ($plan->billing_interval === 'one_time' && $plan->coverage_days) {
            return $start->copy()->addDays((int) $plan->coverage_days);
        }

        if ($interval === 'monthly' || $plan->billing_interval === 'monthly') {
            return $start->copy()->addMonth();
        }

        return $start->copy()->addYear();
    }
}
