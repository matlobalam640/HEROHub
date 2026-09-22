<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Plan;
use App\Models\User;
use App\Services\UsaPayments\UsaPaymentsSubscriptionService;
use App\Support\PortalInvite;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BusinessPlanCheckoutService
{
    /** @var list<string> */
    public const ALLOWED_CATEGORIES = ['business', 'corporate'];

    public function __construct(
        private readonly UsaPaymentsMembershipCheckoutService $checkoutService = new UsaPaymentsMembershipCheckoutService(),
        private readonly CompanyBillingService $billingService = new CompanyBillingService(),
    ) {}

    public function assertBusinessPlan(Plan $plan): void
    {
        if (! $plan->active || ! in_array($plan->category, self::ALLOWED_CATEGORIES, true)) {
            throw ValidationException::withMessages([
                'plan_id' => 'Select an active Small Business or Corporate plan.',
            ]);
        }
    }

    public function seatLimitForPlan(Plan $plan): ?int
    {
        if ($plan->max_members !== null && (int) $plan->max_members > 0) {
            return (int) $plan->max_members;
        }

        if ($plan->min_members !== null && (int) $plan->min_members > 0) {
            return (int) $plan->min_members;
        }

        if ($plan->included_members !== null && (int) $plan->included_members > 0) {
            return (int) $plan->included_members;
        }

        return null;
    }

    public function purchasedSeatCount(Plan $plan): int
    {
        return max(1, (int) ($plan->min_members ?: 1));
    }

    /**
     * @return array{base: float, tax: float, total: float, seats: int}|null
     */
    public function checkoutAmounts(Plan $plan, string $interval): ?array
    {
        $interval = strtolower($interval);
        $unit = null;

        if ($interval === 'monthly') {
            $unit = (float) ($plan->price_monthly ?? 0);
            if ($unit <= 0) {
                $unit = (float) ($plan->price ?? 0);
            }
        } else {
            $unit = (float) ($plan->price ?? 0);
            if ($unit <= 0 && (float) ($plan->price_monthly ?? 0) > 0) {
                $unit = round((float) $plan->price_monthly * 12, 2);
            }
        }

        if ($unit <= 0) {
            return null;
        }

        $seats = $this->purchasedSeatCount($plan);
        $base = round($unit * $seats, 2);
        $taxRate = (float) config('usa_payments.tax_rate', 0.10);
        $tax = round($base * $taxRate, 2);

        return [
            'base' => $base,
            'tax' => $tax,
            'total' => round($base + $tax, 2),
            'seats' => $seats,
        ];
    }

    public function canCheckoutPlan(Plan $plan, string $interval): bool
    {
        if (! UsaPaymentsMembershipCheckoutService::isEnabled()) {
            return false;
        }

        if (! in_array($plan->category, self::ALLOWED_CATEGORIES, true) || ! $plan->active) {
            return false;
        }

        return $this->resolveGatewayPlanId($plan, $interval) !== null
            && $this->checkoutAmounts($plan, $interval) !== null;
    }

    public function resolveGatewayPlanId(Plan $plan, string $interval): ?string
    {
        $mapped = $this->checkoutService->usaPaymentsPlanId($plan, $interval);
        if ($mapped) {
            return $mapped;
        }

        // Fall back to portal plan code so SMB/Corporate can charge once gateway plans exist with the same IDs.
        $code = trim((string) ($plan->code ?? ''));

        return $code !== '' ? $code : null;
    }

    /**
     * @param  array<string, mixed>  $billingAddress
     */
    public function processCheckout(
        Company $company,
        Plan $plan,
        string $interval,
        User $owner,
        string $paymentToken,
        array $billingAddress,
    ): Company {
        if (! $company->isPendingPayment()) {
            throw ValidationException::withMessages([
                'company' => 'This company is already activated.',
            ]);
        }

        $this->assertBusinessPlan($plan);

        $gatewayPlanId = $this->resolveGatewayPlanId($plan, $interval);
        $amounts = $this->checkoutAmounts($plan, $interval);
        if ($gatewayPlanId === null || $amounts === null) {
            throw ValidationException::withMessages([
                'plan_id' => 'This plan is not available for USA Payments checkout.',
            ]);
        }

        $firstName = trim((string) ($billingAddress['first_name'] ?? ''));
        $lastName = trim((string) ($billingAddress['last_name'] ?? ''));
        $email = strtolower(trim((string) ($billingAddress['email'] ?? $owner->email)));
        if ($firstName === '' || $lastName === '' || $email === '') {
            throw ValidationException::withMessages([
                'first_name' => 'Billing name and email are required.',
            ]);
        }

        $response = app(UsaPaymentsSubscriptionService::class)->addSubscription(
            token: $paymentToken,
            planId: $gatewayPlanId,
            email: $email,
            firstName: $firstName,
            lastName: $lastName,
            amount: $amounts['total'],
            tax: $amounts['tax'],
            description: $company->name.' — '.$plan->name,
        );

        $parsed = $response->getParsedResponse();
        if (! $response->isApproved()) {
            Log::warning('B2B USA Payments checkout declined.', [
                'company_id' => $company->id,
                'plan_code' => $plan->code,
                'response_code' => $parsed['response_code'] ?? null,
            ]);

            throw ValidationException::withMessages([
                'payment' => $parsed['response_code_text'] ?: 'Payment was declined. Please check your card details and try again.',
            ]);
        }

        $subscriptionId = trim((string) ($parsed['subscription_id'] ?? $parsed['transactionid'] ?? ''));
        if ($subscriptionId === '') {
            $subscriptionId = 'usa-biz-'.Str::uuid()->toString();
        }

        return DB::transaction(function () use ($company, $plan, $interval, $owner, $subscriptionId, $amounts) {
            $seatLimit = $this->seatLimitForPlan($plan);

            $company->forceFill([
                'enrollment_status' => BusinessEnrollmentService::STATUS_ACTIVE,
                'activated_at' => now(),
                'default_plan_id' => $plan->id,
                'seat_limit' => $seatLimit,
                'billing_provider' => 'usa_payments',
                'billing_subscription_id' => $subscriptionId,
                'subscribed_interval' => $interval,
            ])->save();

            $this->billingService->recalculate($company->fresh());

            PortalInvite::notifyAdmins(
                subject: 'Admin alert: B2B company activated',
                headline: 'A company completed USA Payments checkout and is now active.',
                detailLines: [
                    'Company: '.$company->name,
                    'Plan: '.$plan->name.' ('.$plan->code.')',
                    'Interval: '.$interval,
                    'Charged (incl. tax): $'.number_format($amounts['total'], 2),
                    'Employee seat limit: '.($seatLimit ?? 'unlimited'),
                    'HR: '.$owner->email,
                    'Subscription ID: '.$subscriptionId,
                ],
                actionUrl: route('admin.companies.index', [], true),
                actionLabel: 'Open companies list',
            );

            return $company->fresh(['defaultPlan', 'ownerUser']);
        });
    }
}
