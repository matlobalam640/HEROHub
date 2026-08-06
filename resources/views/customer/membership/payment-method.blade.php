<x-portal-layout>
    <div class="w-full max-w-none space-y-6">
        <div>
            @include('customer.membership.partials.portal-eyebrow')
            <h1 class="font-display mt-1 text-2xl font-semibold tracking-tight text-slate-900">Payment method</h1>
            <p class="mt-1 text-sm text-slate-600">Manage how your membership is billed.</p>
        </div>

        @if (session('status'))
            <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                <ul class="list-disc space-y-1 pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid gap-6 lg:grid-cols-2">
            <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-md shadow-slate-200/50 ring-1 ring-slate-100">
                <div class="hero-panel-header border-b border-slate-100 bg-gradient-to-r from-slate-50 to-[color:var(--dashboard-gold-soft)] px-6 py-4">
                    <div class="text-sm font-semibold text-slate-900">Current billing</div>
                    <div class="text-xs text-slate-500">Membership {{ $membership->membership_number }}</div>
                </div>
                <div class="space-y-3 p-6 text-sm text-slate-700">
                    <div class="flex justify-between gap-3">
                        <span>Provider</span>
                        <span class="font-semibold text-slate-900">{{ ucfirst(str_replace('_', ' ', $membership->billing_provider ?: 'not set')) }}</span>
                    </div>
                    <div class="flex justify-between gap-3">
                        <span>Plan</span>
                        <span class="font-semibold text-slate-900">{{ $membership->plan?->name ?? '—' }}</span>
                    </div>
                    <div class="flex justify-between gap-3">
                        <span>Subscription ID</span>
                        <span class="font-mono text-xs text-slate-900">{{ $membership->billing_subscription_id ?: '—' }}</span>
                    </div>
                    <div class="flex justify-between gap-3">
                        <span>Next billing</span>
                        <span class="font-semibold text-slate-900">{{ $membership->billing_next_billing_at?->format('M j, Y') ?? '—' }}</span>
                    </div>
                    <div class="flex justify-between gap-3">
                        <span>Auto-collect</span>
                        <span class="font-semibold text-slate-900">{{ $membership->billing_auto_collect ? 'Enabled' : 'Disabled' }}</span>
                    </div>
                </div>
            </div>

            @if ($canUpdateCardOnline)
                <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-md shadow-slate-200/50 ring-1 ring-slate-100">
                    <div class="hero-panel-header border-b border-slate-100 bg-gradient-to-r from-slate-50 to-[color:var(--dashboard-gold-soft)] px-6 py-4">
                        <div class="text-sm font-semibold text-slate-900">Update card on file</div>
                        <div class="text-xs text-slate-500">Securely updates your USA Payments recurring subscription</div>
                    </div>
                    <div class="p-6 text-slate-900">
                        <form id="usa-payment-method-form" method="POST" action="{{ route('customer.membership.payment-method.usa-payments') }}" class="space-y-4">
                            @csrf
                            <div class="rounded-xl border border-slate-200 bg-slate-50/80 p-4">
                                <div class="mb-3 text-sm font-semibold text-slate-900">New card details</div>
                                <div id="ccnumber" class="mb-3"></div>
                                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                    <div id="ccexp"></div>
                                    <div id="cvv"></div>
                                </div>
                            </div>
                            <button type="button" id="paybtn"
                                    class="rounded-lg bg-hero-primary px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-hero-primary-hover">
                                Save payment method
                            </button>
                        </form>
                    </div>
                </div>
            @elseif ($membership->billing_provider === 'usa_payments' && blank($membership->billing_subscription_id))
                <div class="rounded-2xl border border-amber-200 bg-amber-50 p-6 text-sm text-amber-950">
                    <p class="font-semibold">No subscription linked yet</p>
                    <p class="mt-2">Complete a renewal checkout first so we can link your USA Payments subscription ID.</p>
                    <a href="{{ route('customer.membership.renew') }}" class="mt-4 inline-flex rounded-lg bg-hero-primary px-4 py-2 text-sm font-semibold text-white hover:bg-hero-primary-hover">
                        Renew / pay now
                    </a>
                </div>
            @endif
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-md shadow-slate-200/50 ring-1 ring-slate-100">
            <div class="hero-panel-header border-b border-slate-100 bg-gradient-to-r from-slate-50 to-[color:var(--dashboard-gold-soft)] px-6 py-4">
                <div class="text-sm font-semibold text-slate-900">Billing metadata</div>
                <div class="text-xs text-slate-500">For staff-managed or legacy billing references</div>
            </div>
            <div class="p-6 text-slate-900">
                <form method="POST" action="{{ route('customer.membership.billing.update') }}" class="grid grid-cols-1 gap-3 rounded-xl border border-slate-200 p-4 sm:max-w-xl">
                    @csrf
                    <div>
                        <label class="text-xs font-medium text-slate-600">Provider</label>
                        <select name="billing_provider" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-hero-primary focus:ring-hero-primary">
                            <option value="">Select provider</option>
                            <option value="stripe" @selected($membership->billing_provider === 'stripe')>Stripe</option>
                            <option value="usa_payments" @selected($membership->billing_provider === 'usa_payments')>USA Payments</option>
                            <option value="manual" @selected($membership->billing_provider === 'manual')>Manual / bank transfer</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-medium text-slate-600">Customer reference</label>
                        <input name="billing_customer_id" value="{{ old('billing_customer_id', $membership->billing_customer_id) }}" placeholder="Billing customer reference" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-hero-primary focus:ring-hero-primary">
                    </div>
                    <button class="rounded-lg bg-slate-800 px-3 py-2 text-sm font-semibold text-white transition hover:bg-slate-900">Save billing metadata</button>
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

    @if ($canUpdateCardOnline)
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
            ></script>
        @endpush
    @endif
</x-portal-layout>
