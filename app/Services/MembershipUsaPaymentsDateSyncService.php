<?php

namespace App\Services;

use App\Models\Membership;
use App\Models\Plan;
use App\Models\User;
use App\Services\UsaPayments\UsaPaymentsQueryService;
use App\Support\UsaPaymentsPlanMapper;
use Carbon\Carbon;

class MembershipUsaPaymentsDateSyncService
{
    public function __construct(
        private readonly UsaPaymentsQueryService $queryService,
    ) {}

    /**
     * @param  list<array<string, mixed>>  $subscriptions
     * @return list<array<string, mixed>>
     */
    public function syncAllPortalMemberships(array $subscriptions, bool $apply = true): array
    {
        $index = $this->buildSubscriptionIndex($subscriptions);
        $rows = [];

        $memberships = Membership::query()
            ->with(['accountUser', 'primaryMember', 'plan'])
            ->orderBy('id')
            ->get();

        foreach ($memberships as $membership) {
            $subscription = $this->findSubscriptionForMembership($membership, $index);
            if ($subscription === null) {
                $rows[] = [
                    'membership_number' => $membership->membership_number,
                    'email' => $this->membershipEmails($membership)[0] ?? '—',
                    'subscription_id' => $membership->billing_subscription_id ?: '—',
                    'coverage_starts_on' => $membership->coverage_starts_on?->toDateString(),
                    'coverage_ends_on' => $membership->coverage_ends_on?->toDateString(),
                    'status' => $membership->status,
                    'matched' => false,
                    'updated' => false,
                    'note' => 'no USA Payments subscription match',
                ];

                continue;
            }

            $rows[] = array_merge(
                $this->syncSubscription($subscription, $apply, $membership),
                ['note' => 'matched']
            );
        }

        return $rows;
    }

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
    public function syncSubscription(array $subscription, bool $apply = true, ?Membership $membership = null): array
    {
        $subscriptionId = (string) ($subscription['subscription_id'] ?? '');
        $email = strtolower(trim((string) ($subscription['email'] ?? '')));

        $dates = $this->resolveCoverageDates($subscription);
        $membership ??= $this->findMembership($subscriptionId, $email, (string) ($subscription['gateway_plan_id'] ?? ''));

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
        $isShortTerm = str_contains($planName, '10 day') || str_contains($planName, '10-day');

        $coverageStart = $firstPayment?->copy();
        if ($coverageStart === null && $nextCharge !== null) {
            $coverageStart = $nextCharge->copy()->subDays($cycleDays);
        } elseif ($coverageStart === null && $lastPayment !== null) {
            $coverageStart = $lastPayment->copy();
        }

        $coverageEnd = $nextCharge?->copy();
        if ($isShortTerm && $coverageStart !== null) {
            $coverageEnd = $coverageStart->copy()->addDays(10);
        } elseif ($coverageEnd === null && $lastPayment !== null) {
            $coverageEnd = $lastPayment->copy()->addDays($cycleDays);
        } elseif ($coverageEnd === null && $coverageStart !== null) {
            $coverageEnd = $coverageStart->copy()->addDays($cycleDays);
        }

        if ($coverageStart !== null && $coverageEnd !== null && $coverageEnd->lte($coverageStart)) {
            $coverageEnd = $coverageStart->copy()->addDays($cycleDays);
        }

        $today = now()->startOfDay();
        $status = 'active';
        if ($coverageEnd !== null && $coverageEnd->lt($today)) {
            $status = 'expired';
        }

        $autoRenew = ! $isShortTerm && ($coverageEnd === null || $coverageEnd->gte($today));

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

    /**
     * @param  list<array<string, mixed>>  $subscriptions
     * @return array{
     *     by_id: array<string, array<string, mixed>>,
     *     by_email: array<string, list<array<string, mixed>>>
     * }
     */
    private function buildSubscriptionIndex(array $subscriptions): array
    {
        $byId = [];
        $byEmail = [];

        foreach ($subscriptions as $subscription) {
            $id = (string) ($subscription['subscription_id'] ?? '');
            if ($id !== '') {
                $byId[$id] = $subscription;
            }

            $email = strtolower(trim((string) ($subscription['email'] ?? '')));
            if ($email !== '') {
                $byEmail[$email] ??= [];
                $byEmail[$email][] = $subscription;
            }
        }

        return ['by_id' => $byId, 'by_email' => $byEmail];
    }

    /**
     * @param  array{
     *     by_id: array<string, array<string, mixed>>,
     *     by_email: array<string, list<array<string, mixed>>>
     * }  $index
     * @return array<string, mixed>|null
     */
    private function findSubscriptionForMembership(Membership $membership, array $index): ?array
    {
        $subscriptionId = trim((string) ($membership->billing_subscription_id ?? ''));
        if ($subscriptionId !== '' && isset($index['by_id'][$subscriptionId])) {
            return $index['by_id'][$subscriptionId];
        }

        $gatewayIds = $this->gatewayPlanIdsForMembership($membership);

        foreach ($this->membershipEmails($membership) as $email) {
            $candidates = $index['by_email'][$email] ?? [];
            if ($candidates === []) {
                continue;
            }

            if (count($candidates) === 1) {
                return $candidates[0];
            }

            foreach ($candidates as $candidate) {
                $candidatePlanId = (string) ($candidate['gateway_plan_id'] ?? '');
                if ($candidatePlanId !== '' && in_array($candidatePlanId, $gatewayIds, true)) {
                    return $candidate;
                }
            }

            return $candidates[0];
        }

        return null;
    }

    /**
     * @return list<string>
     */
    private function membershipEmails(Membership $membership): array
    {
        $emails = array_filter([
            strtolower(trim((string) ($membership->accountUser?->email ?? ''))),
            strtolower(trim((string) ($membership->primaryMember?->email ?? ''))),
        ]);

        return array_values(array_unique($emails));
    }

    /**
     * @return list<string>
     */
    private function gatewayPlanIdsForMembership(Membership $membership): array
    {
        $planCode = trim((string) ($membership->plan?->code ?? ''));
        if ($planCode === '') {
            return [];
        }

        $reverse = config('usa_payments.gateway_to_portal', []);
        $matches = [];
        foreach ($reverse as $gatewayId => $portalCode) {
            if ((string) $portalCode === $planCode) {
                $matches[] = (string) $gatewayId;
            }
        }

        return $matches;
    }

    private function resolveCycleDays(int $dayFrequency, string $planName): int
    {
        if (str_contains($planName, '10 day') || str_contains($planName, '10-day')) {
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
