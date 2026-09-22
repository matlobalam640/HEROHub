<x-portal-layout>
    <div class="w-full max-w-none space-y-6">
        <div>
            <div class="text-sm font-medium text-hero-primary">Business / Corporate</div>
            <h1 class="font-display mt-1 text-2xl font-semibold tracking-tight text-slate-900">Choose a plan &amp; pay</h1>
            <p class="mt-1 text-sm text-slate-600">{{ $company->name }} — Small Business or Corporate plans only. Payment is via USA Payments.</p>
        </div>

        @if ($errors->any())
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                <ul class="list-disc space-y-1 pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($plans->isEmpty())
            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-6 text-sm text-amber-900">
                No Small Business or Corporate plans are currently available for USA Payments checkout. Contact HERO support.
            </div>
        @else
            @php
                $planPayload = $plans->map(function ($plan) {
                    $yearly = app(\App\Services\BusinessPlanCheckoutService::class)->checkoutAmounts($plan, 'yearly');
                    $monthly = app(\App\Services\BusinessPlanCheckoutService::class)->checkoutAmounts($plan, 'monthly');
                    return [
                        'id' => $plan->id,
                        'category' => $plan->category,
                        'label' => $plan->name.' ('.$plan->code.')',
                        'seat_limit' => app(\App\Services\BusinessPlanCheckoutService::class)->seatLimitForPlan($plan),
                        'yearly' => $yearly,
                        'monthly' => $monthly,
                    ];
                })->values();
            @endphp

            <div
                class="grid gap-6 lg:grid-cols-3"
                x-data="{
                    plans: @js($planPayload),
                    planType: @js(old('plan_category', 'business')),
                    planId: @js(old('plan_id', '')),
                    interval: @js(old('interval', 'yearly')),
                    filteredPlans() {
                        return this.plans.filter((p) => p.category === this.planType);
                    },
                    selectedPlan() {
                        return this.plans.find((p) => String(p.id) === String(this.planId)) || null;
                    },
                    amounts() {
                        const plan = this.selectedPlan();
                        if (! plan) return null;
                        return this.interval === 'monthly' ? plan.monthly : plan.yearly;
                    },
                    syncPlan() {
                        const list = this.filteredPlans();
                        if (! list.some((p) => String(p.id) === String(this.planId))) {
                            this.planId = list.length ? String(list[0].id) : '';
                        }
                    },
                }"
                x-init="syncPlan()"
            >
                <div class="lg:col-span-2 overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-md">
                    <div class="border-b border-slate-100 px-6 py-4">
                        <div class="text-sm font-semibold text-slate-900">Plan &amp; payment</div>
                    </div>
                    <form id="usa-payments-form" method="POST" action="{{ route('business.plan-checkout.store') }}" class="space-y-4 p-6">
                        @csrf
                        <input type="hidden" name="payment_token" id="payment_token" value="">

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label class="block text-sm font-medium text-slate-700">Plan type</label>
                                <select x-model="planType" @change="syncPlan()" name="plan_category" class="mt-2 w-full rounded-xl border border-slate-200 text-sm">
                                    <option value="business">Small business</option>
                                    <option value="corporate">Corporate</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700">Plan</label>
                                <select name="plan_id" x-model="planId" required class="mt-2 w-full rounded-xl border border-slate-200 text-sm">
                                    <template x-for="plan in filteredPlans()" :key="plan.id">
                                        <option :value="plan.id" x-text="plan.label"></option>
                                    </template>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Billing interval</label>
                            <select name="interval" x-model="interval" class="mt-2 w-full rounded-xl border border-slate-200 text-sm">
                                <option value="yearly">Annual</option>
                                <option value="monthly">Monthly</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-xs font-medium uppercase tracking-wide text-slate-500">First name</label>
                                <input type="text" name="first_name" required value="{{ old('first_name', strtok($owner->name ?? '', ' ')) }}"
                                       class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-medium uppercase tracking-wide text-slate-500">Last name</label>
                                <input type="text" name="last_name" required value="{{ old('last_name') }}"
                                       class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm">
                            </div>
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium uppercase tracking-wide text-slate-500">Email</label>
                            <input type="email" name="email" required value="{{ old('email', $owner->email) }}"
                                   class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm">
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-xs font-medium uppercase tracking-wide text-slate-500">Phone</label>
                                <input type="text" name="phone" required value="{{ old('phone', $company->phone) }}"
                                       class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-medium uppercase tracking-wide text-slate-500">Country</label>
                                <input type="text" name="country" required value="{{ old('country', $company->country ?: 'USA') }}"
                                       class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-xs font-medium uppercase tracking-wide text-slate-500">State</label>
                                <input type="text" name="state" required value="{{ old('state') }}"
                                       class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-medium uppercase tracking-wide text-slate-500">City</label>
                                <input type="text" name="city" required value="{{ old('city', $company->city) }}"
                                       class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm">
                            </div>
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium uppercase tracking-wide text-slate-500">Street</label>
                            <input type="text" name="street" required value="{{ old('street', $company->address_line1) }}"
                                   class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm">
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium uppercase tracking-wide text-slate-500">ZIP / Postal code</label>
                            <input type="text" name="zip_code" required value="{{ old('zip_code', $company->postal_code) }}"
                                   class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm">
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium uppercase tracking-wide text-slate-500">Card</label>
                            <div id="usa-payments-ccnumber" class="rounded-lg border border-slate-200 bg-white px-3 py-3"></div>
                            <div class="mt-2 grid grid-cols-2 gap-3">
                                <div id="usa-payments-ccexp" class="rounded-lg border border-slate-200 bg-white px-3 py-3"></div>
                                <div id="usa-payments-cvv" class="rounded-lg border border-slate-200 bg-white px-3 py-3"></div>
                            </div>
                            <p id="usa-payments-error" class="mt-2 hidden text-sm text-red-600"></p>
                        </div>

                        <button type="submit" id="usa-payments-submit"
                                class="inline-flex items-center gap-2 rounded-xl bg-hero-primary px-5 py-2.5 text-sm font-semibold text-white hover:bg-hero-primary-hover">
                            Pay &amp; activate company
                        </button>
                    </form>
                </div>

                <div class="space-y-4">
                    <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm">
                        <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Order summary</div>
                        <div class="mt-3 space-y-2 text-sm text-slate-700" x-show="amounts()">
                            <div class="flex justify-between"><span>Seats</span><span x-text="amounts()?.seats ?? '—'"></span></div>
                            <div class="flex justify-between"><span>Subtotal</span><span x-text="amounts() ? ('$' + Number(amounts().base).toFixed(2)) : '—'"></span></div>
                            <div class="flex justify-between"><span>Tax</span><span x-text="amounts() ? ('$' + Number(amounts().tax).toFixed(2)) : '—'"></span></div>
                            <div class="flex justify-between border-t border-slate-100 pt-2 font-semibold text-slate-900">
                                <span>Total</span>
                                <span x-text="amounts() ? ('$' + Number(amounts().total).toFixed(2)) : '—'"></span>
                            </div>
                            <p class="pt-2 text-xs text-slate-500" x-show="selectedPlan()?.seat_limit">
                                Employee seat limit after activation:
                                <strong x-text="selectedPlan()?.seat_limit"></strong>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <script src="{{ $collectJsUrl }}" data-tokenization-key="{{ $tokenizationKey }}"></script>
            <script>
                (function () {
                    const form = document.getElementById('usa-payments-form');
                    const tokenInput = document.getElementById('payment_token');
                    const errorEl = document.getElementById('usa-payments-error');
                    const submitBtn = document.getElementById('usa-payments-submit');
                    if (!form || typeof CollectJS === 'undefined') return;

                    CollectJS.configure({
                        paymentSelector: '#usa-payments-submit',
                        variant: 'inline',
                        fields: {
                            ccnumber: { selector: '#usa-payments-ccnumber', title: 'Card number', placeholder: 'Card number' },
                            ccexp: { selector: '#usa-payments-ccexp', title: 'Exp', placeholder: 'MM / YY' },
                            cvv: { selector: '#usa-payments-cvv', title: 'CVV', placeholder: 'CVV' },
                        },
                        callback: function (response) {
                            if (!response || !response.token) {
                                errorEl.textContent = 'Could not tokenize the card. Please try again.';
                                errorEl.classList.remove('hidden');
                                submitBtn.disabled = false;
                                return;
                            }
                            tokenInput.value = response.token;
                            form.submit();
                        }
                    });

                    form.addEventListener('submit', function (e) {
                        if (!tokenInput.value) {
                            e.preventDefault();
                            submitBtn.disabled = true;
                            errorEl.classList.add('hidden');
                        }
                    });
                })();
            </script>
        @endif
    </div>
</x-portal-layout>
