@props([
    'plan',
    'plansReturn' => 'retail',
])

@php
    use App\Services\StripeMembershipPlanChangeCheckoutService;

    $stripeOn = StripeMembershipPlanChangeCheckoutService::isEnabled();
    $canSubscribe = $stripeOn
        && auth()->check()
        && auth()->user()->hasAnyRole(['customer', 'business']);
    $checkoutMonthlyPriceLabel = $plan->checkoutMonthlyButtonPriceLabel();
    $checkoutYearlyPriceLabel = $plan->checkoutYearlyButtonPriceLabel();
    $monthlyUsd = $plan->unitAmountUsdForStripePlanChange('monthly');
    $yearlyUsd = $plan->unitAmountUsdForStripePlanChange('yearly');
@endphp

@if ($canSubscribe && ($monthlyUsd !== null || $yearlyUsd !== null))
    <div class="mt-auto shrink-0 w-full py-3">
        <div class="h-[5px] shrink-0" aria-hidden="true"></div>
        <div class="border-t border-slate-900/15 pt-[5px] dark:border-white/15">
            <p class="py-2 text-center text-[10px] font-bold uppercase tracking-[0.12em] text-slate-800 dark:text-slate-200">
                Subscribe
            </p>
            <div class="mt-3 flex flex-col gap-3 sm:flex-row sm:items-stretch sm:gap-3">
                @if ($monthlyUsd !== null)
                    <a
                        href="{{ route('customer.membership.plan.subscribe', ['plan' => $plan, 'interval' => 'monthly']) }}"
                        class="inline-flex min-w-0 flex-1 items-center justify-center gap-1.5 rounded-full bg-hero-primary px-4 py-3 text-center text-sm font-semibold text-white shadow-hero-cta transition hover:bg-hero-primary-hover focus:outline-none focus-visible:ring-2 focus-visible:ring-hero-primary/35 focus-visible:ring-offset-2"
                    >
                        <span>Monthly</span>
                        @if ($checkoutMonthlyPriceLabel)
                            <span class="shrink-0 text-[0.8125rem] font-normal opacity-95 tabular-nums">({{ $checkoutMonthlyPriceLabel }})</span>
                        @endif
                    </a>
                @endif
                @if ($yearlyUsd !== null)
                    <a
                        href="{{ route('customer.membership.plan.subscribe', ['plan' => $plan, 'interval' => 'yearly']) }}"
                        class="inline-flex min-w-0 flex-1 items-center justify-center gap-1 rounded-full border-2 border-hero-primary bg-white px-4 py-3 text-center text-sm font-semibold text-hero-primary shadow-sm transition hover:bg-hero-primary hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-hero-primary/35 focus-visible:ring-offset-2"
                    >
                        <span>Annual</span>
                        @if ($checkoutYearlyPriceLabel)
                            <span class="shrink-0 text-[0.8125rem] font-normal tabular-nums">({{ $checkoutYearlyPriceLabel }})</span>
                        @endif
                    </a>
                @endif
            </div>
            <p class="mx-auto mt-5 max-w-md text-center text-[11px] leading-snug text-slate-800 dark:text-slate-200">
                Secure payment with Stripe. TCA tax may apply at payment.
            </p>
        </div>
    </div>
@endif
