@php
    use Illuminate\Support\Js;

    $stripePlanCheckoutEnabled = $stripePlanCheckoutEnabled ?? false;

    $planOptions = $availablePlans->map(function ($p) use ($stripePlanCheckoutEnabled) {
        $stripeMonthly = $p->unitAmountUsdForStripePlanChange('monthly') !== null;
        $stripeYearly = $p->unitAmountUsdForStripePlanChange('yearly') !== null;

        return [
            'id' => (int) $p->id,
            'label' => $p->name.' ('.ucfirst((string) $p->category).')',
            'priceLine' => $p->catalogPrimaryPriceLine(),
            'hasMonthly' => $stripeMonthly,
            'hasYearly' => $stripeYearly,
            'onlineCheckout' => $stripePlanCheckoutEnabled && ($stripeMonthly || $stripeYearly),
        ];
    })->values()->all();

    $currentPlanRow = $availablePlans->firstWhere('id', $membership->plan_id);
    $currentPlanLabel = $currentPlanRow
        ? $currentPlanRow->name.' ('.ucfirst((string) $currentPlanRow->category).')'
        : ($membership->plan
            ? $membership->plan->name.' ('.ucfirst((string) $membership->plan->category).')'
            : '—');

    $currentPriceLine = $currentPlanRow
        ? $currentPlanRow->catalogPrimaryPriceLine()
        : ($membership->plan?->catalogPrimaryPriceLine() ?? '—');

    $selectedId = (int) ($membership->plan_id ?: ($availablePlans->first()?->id ?? 0));
    $defaultInterval = $membership->plan?->billing_interval === 'monthly' ? 'monthly' : 'yearly';
@endphp

<x-portal-layout>
    <div class="w-full max-w-none space-y-6">
        <div>
            @include('customer.membership.partials.portal-eyebrow')
            <h1 class="font-display mt-1 text-2xl font-semibold tracking-tight text-slate-900">Upgrade / downgrade plan</h1>
            <p class="mt-1 text-sm text-slate-600">
                @if ($stripePlanCheckoutEnabled)
                    Pay with Stripe after you choose a plan.
                    @if (filled(config('heroportal.zoho_customer_portal_url')))
                        Use the
                        <a
                            href="{{ config('heroportal.zoho_customer_portal_url') }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="font-medium text-hero-primary underline decoration-hero-primary/40 underline-offset-2 hover:text-hero-primary-hover"
                        >
                            billing portal
                            <span class="sr-only">(opens in a new tab)</span>
                        </a>
                        to cancel an old subscription if needed.
                    @endif
                @else
                    Set <code class="rounded bg-slate-100 px-1 py-0.5 font-mono text-xs">STRIPE_SECRET</code> in <code class="rounded bg-slate-100 px-1 py-0.5 font-mono text-xs">.env</code> to enable checkout.
                @endif
            </p>
        </div>

        @if (session('status'))
            <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                {{ session('status') }}
            </div>
        @endif

        <div class="overflow-visible rounded-2xl border border-slate-200/80 bg-white shadow-md shadow-slate-200/50 ring-1 ring-slate-100">
            <div class="hero-panel-header overflow-hidden rounded-t-2xl border-b border-slate-100 bg-gradient-to-r from-slate-50 to-[color:var(--dashboard-gold-soft)] px-6 py-4">
                <div class="text-sm font-semibold text-slate-900">Plan selection</div>
                <div class="text-xs text-slate-500">Current plan: {{ $membership->plan?->name ?? '—' }}</div>
            </div>
            <div class="p-6 text-slate-900">
                <form
                    method="POST"
                    action="{{ route('customer.membership.plan.update') }}"
                    class="grid grid-cols-1 gap-3 rounded-xl border border-slate-200 p-4 sm:max-w-xl"
                    x-data="{
                        open: false,
                        currentPlanId: {{ (int) $membership->plan_id }},
                        selectedId: {{ $selectedId }},
                        selectedLabel: {{ Js::from($currentPlanLabel) }},
                        selectedPriceLine: {{ Js::from($currentPriceLine) }},
                        selectedInterval: {{ Js::from($defaultInterval) }},
                        plans: {{ Js::from($planOptions) }},
                        planById(id) {
                            return this.plans.find(p => Number(p.id) === Number(id));
                        },
                        hasCheckoutFor(id) {
                            const p = this.planById(id);
                            return !!(p && p.onlineCheckout);
                        },
                        selectionIsCurrentPlan() {
                            return Number(this.selectedId) === Number(this.currentPlanId);
                        },
                        ensureIntervalFits() {
                            const p = this.planById(this.selectedId);
                            if (!p) return;
                            if (this.selectedInterval === 'monthly' && !p.hasMonthly) {
                                this.selectedInterval = p.hasYearly ? 'yearly' : 'monthly';
                            }
                            if (this.selectedInterval === 'yearly' && !p.hasYearly) {
                                this.selectedInterval = p.hasMonthly ? 'monthly' : 'yearly';
                            }
                        },
                        pick(item) {
                            this.selectedId = item.id;
                            this.selectedLabel = item.label;
                            this.selectedPriceLine = item.priceLine;
                            this.open = false;
                            this.ensureIntervalFits();
                        },
                    }"
                    x-init="ensureIntervalFits()"
                    @keydown.escape.window="open = false"
                >
                    @csrf
                    <input type="hidden" name="interval" x-bind:value="selectedInterval">

                    <div class="relative z-30">
                        <label id="plan-select-label" for="plan_id" class="text-xs font-medium text-slate-600">Plan</label>
                        <input type="hidden" name="plan_id" id="plan_id" x-model="selectedId">

                        <button
                            type="button"
                            class="mt-1 flex w-full items-center justify-between gap-2 rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-left shadow-sm ring-hero-primary/0 transition hover:border-slate-400 focus:border-hero-primary focus:outline-none focus:ring-2 focus:ring-hero-primary/25"
                            @click="open = !open"
                            :aria-expanded="open"
                            aria-haspopup="listbox"
                            aria-controls="plan-select-listbox"
                            aria-labelledby="plan-select-label"
                        >
                            <div class="min-w-0 flex-1">
                                <div class="truncate text-sm font-medium text-slate-900" x-text="selectedLabel"></div>
                                <div class="truncate text-xs text-slate-500 tabular-nums" x-text="selectedPriceLine"></div>
                            </div>
                            <i class="fa-solid fa-chevron-down shrink-0 transform self-center text-xs text-slate-500 transition-transform duration-200" :class="open && 'rotate-180'" aria-hidden="true"></i>
                        </button>

                        <div
                            x-show="open"
                            x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="opacity-0 translate-y-0.5"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="opacity-100 translate-y-0"
                            x-transition:leave-end="opacity-0 translate-y-0.5"
                            @click.outside="open = false"
                            x-cloak
                            class="absolute left-0 right-0 top-full z-50 mt-1"
                        >
                            <div
                                id="plan-select-listbox"
                                class="hero-plan-dropdown-scroll rounded-xl border border-slate-200 bg-white py-1 shadow-lg ring-1 ring-slate-900/5"
                                role="listbox"
                                aria-labelledby="plan-select-label"
                                style="max-height: 240px; overflow-y: scroll; overflow-x: hidden; overscroll-behavior: contain; -webkit-overflow-scrolling: touch;"
                            >
                                <template x-for="item in plans" :key="item.id">
                                    <button
                                        type="button"
                                        role="option"
                                        class="flex w-full items-start justify-between gap-3 px-3 py-2.5 text-left text-sm hover:bg-slate-50"
                                        :class="Number(selectedId) === Number(item.id) ? 'bg-hero-primary-soft font-semibold text-hero-primary' : 'text-slate-800'"
                                        :aria-selected="Number(selectedId) === Number(item.id)"
                                        @click="pick(item)"
                                    >
                                        <span class="min-w-0 flex-1 font-medium leading-snug" x-text="item.label"></span>
                                        <span class="shrink-0 text-right text-xs tabular-nums leading-snug" :class="Number(selectedId) === Number(item.id) ? 'text-hero-primary' : 'text-slate-600'" x-text="item.priceLine"></span>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>

                    <fieldset
                        x-show="hasCheckoutFor(selectedId)"
                        x-cloak
                        class="mt-1 space-y-2 rounded-xl border border-slate-200 bg-slate-50/60 px-3 py-3"
                    >
                        <legend class="px-1 text-xs font-medium text-slate-600">
                            {{ $stripePlanCheckoutEnabled ? 'Billing cycle' : 'Billing cycle for checkout' }}
                        </legend>
                        <div class="flex flex-wrap gap-4">
                            <label
                                x-show="planById(selectedId)?.hasMonthly"
                                class="inline-flex cursor-pointer items-center gap-2 text-sm text-slate-800"
                            >
                                <input type="radio" class="text-hero-primary focus:ring-hero-primary" value="monthly" x-model="selectedInterval">
                                Monthly
                            </label>
                            <label
                                x-show="planById(selectedId)?.hasYearly"
                                class="inline-flex cursor-pointer items-center gap-2 text-sm text-slate-800"
                            >
                                <input type="radio" class="text-hero-primary focus:ring-hero-primary" value="yearly" x-model="selectedInterval">
                                Annual
                            </label>
                        </div>
                    </fieldset>

                    <button
                        type="submit"
                        class="rounded-lg bg-hero-primary px-3 py-2 text-sm font-semibold text-white hover:bg-hero-primary-hover disabled:cursor-not-allowed disabled:opacity-60"
                        :disabled="selectionIsCurrentPlan()"
                    >
                        <span x-show="selectionIsCurrentPlan()" x-cloak>Current plan</span>
                        @if ($stripePlanCheckoutEnabled)
                            <span x-show="!selectionIsCurrentPlan() && hasCheckoutFor(selectedId)" x-cloak>Continue to secure payment</span>
                        @else
                            <span x-show="!selectionIsCurrentPlan() && hasCheckoutFor(selectedId)" x-cloak>Continue to checkout</span>
                        @endif
                        <span x-show="!selectionIsCurrentPlan() && !hasCheckoutFor(selectedId)" x-cloak>
                            @if ($stripePlanCheckoutEnabled)
                                Save plan in portal
                            @else
                                Update plan
                            @endif
                        </span>
                    </button>
                </form>

                <div class="mt-5">
                    <a href="{{ route('customer.membership') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-700 shadow-sm transition hover:border-hero-primary hover:text-hero-primary">
                        <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                        Back to membership
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-portal-layout>
