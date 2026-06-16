<?php

namespace App\Services;

use App\Models\Member;
use App\Models\Membership;
use App\Models\Partner;
use App\Models\PartnerSale;
use App\Models\Plan;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PartnerEnrollmentService
{
    /**
     * Create a retail membership attributed to the partner and record commission on the sale.
     *
     * @param  array{first_name: string, last_name: string, email: string, phone?: ?string, plan_id: int}  $data
     */
    public function enroll(Partner $partner, array $data): Membership
    {
        return DB::transaction(function () use ($partner, $data) {
            $plan = Plan::query()
                ->whereKey($data['plan_id'])
                ->where('category', 'retail')
                ->where('active', true)
                ->first();

            if (! $plan) {
                throw ValidationException::withMessages([
                    'plan_id' => __('Select a valid retail plan.'),
                ]);
            }

            $saleAmount = (float) ($plan->price ?? 0);
            $commissionPercent = (float) ($partner->commission_percent ?? 8.0);
            $commissionAmount = round($saleAmount * ($commissionPercent / 100), 2);

            $email = strtolower(trim($data['email']));

            $user = User::query()->where('email', $email)->first();

            if ($user) {
                if ($user->hasRole('admin')) {
                    throw ValidationException::withMessages([
                        'email' => __('This email cannot be used for a new enrollment.'),
                    ]);
                }

                $hasActive = Membership::query()
                    ->where('account_user_id', $user->id)
                    ->where('status', 'active')
                    ->exists();

                if ($hasActive) {
                    throw ValidationException::withMessages([
                        'email' => __('This person already has an active membership.'),
                    ]);
                }
            } else {
                $user = User::create([
                    'name' => trim($data['first_name'].' '.$data['last_name']),
                    'email' => $email,
                    'password' => Hash::make(Str::random(40)),
                ]);
                $user->assignRole('customer');
            }

            if (! $user->hasRole('customer')) {
                $user->assignRole('customer');
            }

            $starts = now()->startOfDay();
            $ends = $this->coverageEndsOn($starts, $plan);

            $membership = Membership::create([
                'membership_number' => 'HERO-PR-'.strtoupper(Str::random(10)),
                'plan_id' => $plan->id,
                'account_user_id' => $user->id,
                'company_id' => null,
                'partner_id' => $partner->id,
                'coverage_starts_on' => $starts->toDateString(),
                'coverage_ends_on' => $ends->toDateString(),
                'auto_renew' => true,
                'status' => 'active',
            ]);

            Member::create([
                'membership_id' => $membership->id,
                'is_primary' => true,
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'phone' => $data['phone'] ?? null,
                'email' => $email,
                'qr_token' => (string) Str::uuid(),
            ]);

            PartnerSale::create([
                'partner_id' => $partner->id,
                'membership_id' => $membership->id,
                'plan_id' => $plan->id,
                'sale_amount' => $saleAmount,
                'commission_percent' => $commissionPercent,
                'commission_amount' => $commissionAmount,
                'sold_at' => now(),
            ]);

            return $membership;
        });
    }

    private function coverageEndsOn(CarbonInterface $start, Plan $plan): CarbonInterface
    {
        if ($plan->coverage_days) {
            return $start->copy()->addDays((int) $plan->coverage_days);
        }

        return match ($plan->billing_interval) {
            'monthly' => $start->copy()->addMonth(),
            'yearly', 'one_time' => $start->copy()->addYear(),
            default => $start->copy()->addYear(),
        };
    }
}
