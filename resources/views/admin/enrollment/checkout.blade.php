<x-portal-layout>
    <div class="w-full max-w-none space-y-6">
        <div>
            <div class="text-sm font-medium text-hero-primary">Dispatch · Walk-in member</div>
            <h1 class="font-display mt-1 text-2xl font-semibold tracking-tight text-slate-900">Collect payment</h1>
            <p class="mt-1 text-sm text-slate-600">Charge the member's card to activate membership <span class="font-mono">{{ $membership->membership_number }}</span>.</p>
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

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2 overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-md shadow-slate-200/50 ring-1 ring-slate-100">
                <div class="hero-panel-header border-b border-slate-100 bg-gradient-to-r from-slate-50 to-[color:var(--dashboard-gold-soft)] px-6 py-4">
                    <div class="text-sm font-semibold text-slate-900">Payment details</div>
                    <div class="text-xs text-slate-500">Visa, MasterCard, Discover, American Express, and more</div>
                </div>
                <div class="p-6 text-slate-900">
                    <form id="usa-payments-form" method="POST" action="{{ route('admin.enrollment.checkout.submit') }}" class="space-y-4">
                        @csrf
                        <input type="hidden" name="token" value="{{ $token }}">

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-xs font-medium uppercase tracking-wide text-slate-500">First name</label>
                                <input type="text" name="first_name" required value="{{ old('first_name', $primary?->first_name) }}"
                                       class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:border-hero-primary focus:outline-none">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-medium uppercase tracking-wide text-slate-500">Last name</label>
                                <input type="text" name="last_name" required value="{{ old('last_name', $primary?->last_name) }}"
                                       class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:border-hero-primary focus:outline-none">
                            </div>
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium uppercase tracking-wide text-slate-500">Email</label>
                            <input type="email" name="email" required value="{{ old('email', $prefillEmail) }}"
                                   class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:border-hero-primary focus:outline-none">
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-xs font-medium uppercase tracking-wide text-slate-500">Phone</label>
                                <input type="text" name="phone" required value="{{ old('phone', $primary?->phone) }}"
                                       class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:border-hero-primary focus:outline-none">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-medium uppercase tracking-wide text-slate-500">Company</label>
                                <input type="text" name="company" value="{{ old('company', 'Individual') }}"
                                       class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:border-hero-primary focus:outline-none">
                            </div>
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium uppercase tracking-wide text-slate-500">Industry</label>
                            <input type="text" name="industry" value="{{ old('industry', 'Membership') }}"
                                   class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:border-hero-primary focus:outline-none">
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-xs font-medium uppercase tracking-wide text-slate-500">Country</label>
                                <input type="text" name="country" required value="{{ old('country', $primary?->country ?: 'USA') }}"
                                       class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:border-hero-primary focus:outline-none">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-medium uppercase tracking-wide text-slate-500">State</label>
                                <input type="text" name="state" required value="{{ old('state') }}"
                                       class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:border-hero-primary focus:outline-none">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-xs font-medium uppercase tracking-wide text-slate-500">City</label>
                                <input type="text" name="city" required value="{{ old('city', $primary?->city) }}"
                                       class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:border-hero-primary focus:outline-none">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-medium uppercase tracking-wide text-slate-500">Zip code</label>
                                <input type="text" name="zip_code" required value="{{ old('zip_code') }}"
                                       class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:border-hero-primary focus:outline-none">
                            </div>
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium uppercase tracking-wide text-slate-500">Street address</label>
                            <input type="text" name="street" required value="{{ old('street') }}"
                                   class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:border-hero-primary focus:outline-none">
                        </div>

                        <div class="rounded-xl border border-slate-200 bg-slate-50/80 p-4">
                            <div class="mb-3 text-sm font-semibold text-slate-900">Card details</div>
                            <div id="ccnumber" class="mb-3"></div>
                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                <div id="ccexp"></div>
                                <div id="cvv"></div>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-3 pt-2">
                            <button type="button" id="paybtn"
                                    class="rounded-lg bg-hero-primary px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-hero-primary-hover">
                                Pay ${{ number_format($amounts['total'], 2) }}
                            </button>
                            <a href="{{ route('admin.enrollment.index') }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:border-slate-400">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-md shadow-slate-200/50 ring-1 ring-slate-100">
                <div class="hero-panel-header border-b border-slate-100 bg-gradient-to-r from-slate-50 to-[color:var(--dashboard-gold-soft)] px-6 py-4">
                    <div class="text-sm font-semibold text-slate-900">Order summary</div>
                    <div class="text-xs text-slate-500">Pending until payment succeeds</div>
                </div>
                <div class="space-y-3 p-6 text-sm text-slate-700">
                    <div class="flex justify-between gap-3">
                        <span>{{ $plan->name }}</span>
                        <span class="font-semibold tabular-nums text-slate-900">${{ number_format($amounts['base'], 2) }}</span>
                    </div>
                    <div class="flex justify-between gap-3">
                        <span>Billing</span>
                        <span class="font-medium text-slate-900">{{ $intervalLabel }}</span>
                    </div>
                    <div class="flex justify-between gap-3">
                        <span>Tax ({{ number_format((float) config('usa_payments.tax_rate', 0.10) * 100, 0) }}%)</span>
                        <span class="font-semibold tabular-nums text-slate-900">${{ number_format($amounts['tax'], 2) }}</span>
                    </div>
                    <div class="flex justify-between gap-3 border-t border-slate-200 pt-4 text-base font-bold text-slate-900">
                        <span>Total</span>
                        <span class="tabular-nums text-hero-primary">${{ number_format($amounts['total'], 2) }}</span>
                    </div>
                    <p class="border-t border-slate-100 pt-4 text-xs text-slate-500">
                        Membership # <span class="font-mono">{{ $membership->membership_number }}</span> will activate when payment is approved.
                    </p>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script
            src="{{ config('usa_payments.collect_js_url') }}"
            data-tokenization-key="{{ config('usa_payments.tokenization_key') }}"
            data-payment-selector="#paybtn"
            data-variant="inline"
            data-style-sniffer="false"
            data-field-cvv-display="required"
            data-field-ccnumber-selector="#ccnumber"
            data-field-ccnumber-title="Card number"
            data-field-ccnumber-placeholder="Card number"
            data-field-ccexp-selector="#ccexp"
            data-field-ccexp-title="Expiration"
            data-field-ccexp-placeholder="MM / YY"
            data-field-cvv-selector="#cvv"
            data-field-cvv-title="CVV"
            data-field-cvv-placeholder="CVV"
            data-currency="USD"
            data-country="US"
            data-custom-css='{"padding":"0.625rem 0.75rem","background-color":"#ffffff","color":"#0f172a","width":"100%","font-size":"0.875rem","border":"1px solid #e2e8f0","border-radius":"0.5rem","outline":"0"}'
            data-invalid-css='{"background-color":"#fef2f2","padding":"0.625rem 0.75rem","color":"#0f172a","width":"100%","font-size":"0.875rem","border":"1px solid #fecaca","border-radius":"0.5rem","outline":"0"}'
            data-valid-css='{"background-color":"#f0fdf4","padding":"0.625rem 0.75rem","color":"#0f172a","width":"100%","font-size":"0.875rem","border":"1px solid #bbf7d0","border-radius":"0.5rem","outline":"0"}'
        ></script>
    @endpush
</x-portal-layout>
