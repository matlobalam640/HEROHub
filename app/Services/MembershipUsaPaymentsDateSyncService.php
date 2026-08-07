<?php

namespace App\Services;

use App\Models\Membership;
use App\Models\Plan;
use App\Models\User;
use App\Services\UsaPayments\UsaPaymentsQueryService;
use App\Support\UsaPaymentsPlanMapper;
use Carbon\Carbon;
use Illuminate\Support\Str;

class MembershipUsaPaymentsDateSyncService
{
    public function __construct(
        private readonly UsaPaymentsQueryService $queryService,
    ) {}

    /**
     * @return array{
     *     subscription_id: string,
     *     email: string,
     *     membership_id: int|null,
     *     membership_number: string|null,
     *     coverage_starts_on: string|null,
     *     coverage_ends_on: string|null,
     *     billing_next_billing_at: string|null,
     *     billing_last_billing_at: string|null,
     *     status: string,
     *     matched: bool,
     *     updated: bool
     * }
     */
    public function syncSubscription(array $subscription, bool $apply = true): array
    {
        $subscriptionId = (string) ($subscription['subscription_id'] ?? '');
        $email = strtolower(trim((string) ($subscription['email'] ?? '')));

        $dates = $this->resolveCoverageDates($subscription);
        $membership = $this->findMembership($subscriptionId, $email, (string) ($subscription['gateway_plan_id'] ?? ''));

        $result = [
            'subscription_id' => $subscriptionId,
            'email' => $email,
            'membership_id' => $membership?->id,
            'membership_number' => $membership?->membership_number,
            'coverage_starts_on' => $dates['coverage_starts_on']?->toDateString(),
            'coverage_ends_on' => $dates['coverage_ends_on']?->toDateString(),
            'billing_next_billing_at' => $dates['billing_next_billing_at']?->toDateString(),
            'billing_last_billing_at' => $dates['billing_last_billing_at']?->toDateString(),
            'status' => $dates['status'],
            'matched' => $membership !== null,
            'updated' => false,
        ];

        if ($membership === null || ! $apply) {
            return $result;
        }

        $membership->fill([
            'coverage_starts_on' => $dates['coverage_starts_on'],
            'coverage_ends_on' => $dates['coverage_ends_on'],
            'billing_next_billing_at' => $dates['billing_next_billing_at'],
            'billing_last_billing_at' => $dates['billing_last_billing_at'],
            'billing_subscription_created_at' => $dates['billing_subscription_created_at'],
            'billing_provider' => 'usa_payments',
            'billing_subscription_id' => $subscriptionId,
            'status' => $dates['status'],
            'auto_renew' => $dates['auto_renew'],
        ]);

        if ($membership->isDirty()) {
            $membership->save();
            $result['updated'] = true;
        }

        return $result;
    }

    /**
     * @param  array<string, mixed>  $subscription
     * @return array{
     *     coverage_starts_on: ?Carbon,
     *     coverage_ends_on: ?Carbon,
     *     billing_next_billing_at: ?Carbon,
     *     billing_last_billing_at: ?Carbon,
     *     billing_subscription_created_at: ?Carbon,
     *     status: string,
     *     auto_renew: bool
     * }
     */
    public function resolveCoverageDates(array $subscription): array
    {
        $subscriptionId = (string) ($subscription['subscription_id'] ?? '');
        $planName = strtolower((string) ($subscription['plan_name'] ?? ''));
        $dayFrequency = (int) ($subscription['day_frequency'] ?? 0);
        $nextCharge = $subscription['next_charge_date'] ?? null;
        $nextCharge = $nextCharge instanceof Carbon ? $nextCharge->copy() : null;

        $firstPayment = $this->queryService->firstSuccessfulPaymentDate($subscriptionId);
        $lastPayment = $this->queryService->lastSuccessfulPaymentDate($subscriptionId);

        $cycleDays = $this->resolveCycleDays($dayFrequency, $planName);
        $isShortTerm = $cycleDays <= 31 || str_contains($planName, '10 day');

        $coverageEnd = $nextCharge;
        if ($coverageEnd === null && $lastPayment !== null) {
            $coverageEnd = $lastPayment->copy()->addDays($cycleDays);
        }

        $coverageStart = null;
        if ($coverageEnd !== null && ! $isShortTerm) {
            $coverageStart = $coverageEnd->copy()->subDays($cycleDays);
        } elseif ($firstPayment !== null) {
            $coverageStart = $firstPayment->copy();
            if ($coverageEnd === null) {
                $coverageEnd = $coverageStart->copy()->addDays($cycleDays);
            }
        } elseif ($lastPayment !== null) {
            $coverageStart = $lastPayment->copy();
            if ($coverageEnd === null) {
                $coverageEnd = $coverageStart->copy()->addDays($cycleDays);
            }
        }

        if ($coverageStart !== null && $coverageEnd !== null && $coverageEnd->lte($coverageStart)) {
            $coverageEnd = $coverageStart->copy()->addDays($cycleDays);
        }

        $today = now()->startOfDay();
        $status = 'active';
        if ($coverageEnd !== null && $coverageEnd->lt($today)) {
            $status = 'expired';
        }

        $autoRenew = ! $isShortTerm || ($coverageEnd !== null && $coverageEnd->gte($today));

        return [
            'coverage_starts_on' => $coverageStart?->startOfDay(),
            'coverage_ends_on' => $coverageEnd?->startOfDay(),
            'billing_next_billing_at' => $nextCharge?->startOfDay(),
            'billing_last_billing_at' => ($lastPayment ?? $firstPayment)?->startOfDay(),
            'billing_subscription_created_at' => $firstPayment,
            'status' => $status,
            'auto_renew' => $autoRenew,
        ];
    }

    private function resolveCycleDays(int $dayFrequency, string $planName): int
    {
        if (str_contains($planName, '10 day')) {
            return 10;
        }

        if (str_contains($planName, '1 month') || str_contains($planName, '1-month') || str_contains($planName, 'monthly')) {
            return 30;
        }

        return match ($dayFrequency) {
            30 => 30,
            365 => 365,
            default => 365,
        };
    }

    private function findMembership(string $subscriptionId, string $email, string $gatewayPlanId): ?Membership
    {
        $membership = Membership::query()
            ->where('billing_subscription_id', $subscriptionId)
            ->first();

        if ($membership !== null) {
            return $membership;
        }

        if ($email === '') {
            return null;
        }

        $user = User::query()->whereRaw('LOWER(email) = ?', [$email])->first();
        if ($user === null) {
            return null;
        }

        $planCode = UsaPaymentsPlanMapper::portalPlanCodeFromGatewayPlanId($gatewayPlanId);
        $planQuery = Membership::query()->where('account_user_id', $user->id);

        if (is_string($planCode) && $planCode !== '') {
            $planId = Plan::query()->where('code', $planCode)->value('id');
            if ($planId) {
                $byPlan = (clone $planQuery)->where('plan_id', $planId)->orderByDesc('id')->first();
                if ($byPlan !== null) {
                    return $byPlan;
                }
            }
        }

        return $planQuery->orderByDesc('id')->first();
    }
}
