<?php

namespace App\Services;

use App\Models\Membership;
use App\Models\Plan;
use App\Models\User;
use App\Services\UsaPayments\UsaPaymentsSubscriptionService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class UsaPaymentsMembershipCheckoutService
{
    public const REVIEW_CACHE_PREFIX = 'membership_usa_payments_checkout:';

    public static function isEnabled(): bool
    {
        return filled(config('usa_payments.security_key'))
            && filled(config('usa_payments.tokenization_key'));
    }

    public function usaPaymentsPlanId(Plan $plan, string $interval): ?string
    {
        $mapping = config('usa_payments.plan_ids.'.($plan->code ?? ''), []);
        if (! is_array($mapping) || $mapping === []) {
            return null;
        }

        $interval = strtolower($interval);
        if ($plan->billing_interval === 'one_time') {
            return $mapping['onetime'] ?? null;
        }

        if ($interval === 'monthly') {
            return $mapping['monthly'] ?? null;
        }

        if ($interval === 'yearly') {
            return $mapping['yearly'] ?? null;
        }

        return null;
    }

    /**
     * @return array{base: float, tax: float, total: float}|null
     */
    public function checkoutAmounts(Plan $plan, string $interval): ?array
    {
        $base = $plan->unitAmountUsdForStripePlanChange($interval);
        if ($base === null && $plan->billing_interval === 'one_time') {
            $base = (float) ($plan->price ?? 0);
            $base = $base > 0 ? round($base, 2) : null;
        }

        if ($base === null || $base <= 0) {
            return null;
        }

        $taxRate = (float) config('usa_payments.tax_rate', 0.10);
        $tax = round($base * $taxRate, 2);

        return [
            'base' => $base,
            'tax' => $tax,
            'total' => round($base + $tax, 2),
        ];
    }

    public function canCheckoutPlan(Plan $plan, string $interval): bool
    {
        if (! self::isEnabled()) {
            return false;
        }

        return $this->usaPaymentsPlanId($plan, $interval) !== null
            && $this->checkoutAmounts($plan, $interval) !== null;
    }

    /**
     * @param  array<string, mixed>  $billingAddress
     */
    public function processCheckout(
        Membership $membership,
        Plan $plan,
        string $interval,
        User $user,
        string $paymentToken,
        array $billingAddress,
    ): Membership {
        $usaPlanId = $this->usaPaymentsPlanId($plan, $interval);
        $amounts = $this->checkoutAmounts($plan, $interval);

        if ($usaPlanId === null || $amounts === null) {
            throw ValidationException::withMessages([
                'plan' => 'This plan is not available for USA Payments checkout.',
            ]);
        }

        $primary = $membership->members->firstWhere('is_primary', true) ?? $membership->members->first();
        $firstName = trim((string) ($billingAddress['first_name'] ?? $primary?->first_name ?? ''));
        $lastName = trim((string) ($billingAddress['last_name'] ?? $primary?->last_name ?? ''));
        if ($firstName === '' || $lastName === '') {
            throw ValidationException::withMessages([
                'first_name' => 'First and last name are required.',
            ]);
        }

        $email = trim((string) ($billingAddress['email'] ?? $user->email));
        if ($email === '') {
            throw ValidationException::withMessages(['email' => 'Email is required.']);
        }

        $gateway = app(UsaPaymentsSubscriptionService::class);
        $response = $gateway->addSubscription(
            token: $paymentToken,
            planId: $usaPlanId,
            email: $email,
            firstName: $firstName,
            lastName: $lastName,
            amount: $amounts['total'],
            tax: $amounts['tax'],
            description: $plan->name,
        );

        $parsed = $response->getParsedResponse();
        if (! $response->isApproved()) {
            Log::warning('USA Payments checkout declined.', [
                'membership_id' => $membership->id,
                'plan_code' => $plan->code,
                'response_code' => $parsed['response_code'] ?? null,
            ]);

            throw ValidationException::withMessages([
                'payment' => $parsed['response_code_text'] ?: 'Payment was declined. Please check your card details and try again.',
            ]);
        }

        $subscriptionId = trim((string) ($parsed['subscription_id'] ?? $parsed['transactionid'] ?? ''));
        if ($subscriptionId === '') {
            $subscriptionId = 'usa-'.Str::uuid()->toString();
        }

        [$coverageStart, $coverageEnd, $nextBillingAt] = $this->resolveCoverageWindow($membership, $plan, $interval);

        return DB::transaction(function () use (
            $membership,
            $plan,
            $subscriptionId,
            $coverageStart,
            $coverageEnd,
            $nextBillingAt,
        ) {
            $membership->update([
                'plan_id' => $plan->id,
                'status' => 'active',
                'auto_renew' => true,
                'billing_provider' => 'usa_payments',
                'billing_subscription_id' => $subscriptionId,
                'coverage_starts_on' => $coverageStart,
                'coverage_ends_on' => $coverageEnd,
                'billing_subscription_created_at' => $membership->billing_subscription_created_at ?? now(),
                'billing_last_billing_at' => now()->toDateString(),
                'billing_next_billing_at' => $nextBillingAt,
                'billing_auto_collect' => true,
            ]);

            return $membership->fresh(['plan', 'members']);
        });
    }

    /**
     * @return array{0: Carbon, 1: Carbon, 2: Carbon|null}
     */
    private function resolveCoverageWindow(Membership $membership, Plan $plan, string $interval): array
    {
        $today = now()->startOfDay();
        $currentEnd = $membership->coverage_ends_on?->copy()->startOfDay();
        $start = ($currentEnd && $currentEnd->greaterThanOrEqualTo($today))
            ? $currentEnd->copy()
            : $today->copy();

        if ($plan->billing_interval === 'one_time' && $plan->coverage_days) {
            $end = $start->copy()->addDays((int) $plan->coverage_days);

            return [$start, $end, null];
        }

        if ($interval === 'monthly' || ($plan->billing_interval === 'monthly' && $interval !== 'yearly')) {
            $end = $start->copy()->addMonth();
            $nextBilling = $end->copy();

            return [$start, $end, $nextBilling];
        }

        $end = $start->copy()->addYear();
        $nextBilling = $end->copy();

        return [$start, $end, $nextBilling];
    }
}
