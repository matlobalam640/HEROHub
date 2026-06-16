<x-portal-layout>
    <div class="w-full max-w-none space-y-6">
        <div>
            @include('customer.membership.partials.portal-eyebrow')
            <h1 class="font-display mt-1 text-2xl font-semibold tracking-tight text-slate-900">Payment method</h1>
            <p class="mt-1 text-sm text-slate-600">Update your billing provider and customer reference.</p>
        </div>

        @if (session('status'))
            <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                {{ session('status') }}
            </div>
        @endif

        <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-md shadow-slate-200/50 ring-1 ring-slate-100">
            <div class="hero-panel-header border-b border-slate-100 bg-gradient-to-r from-slate-50 to-[color:var(--dashboard-gold-soft)] px-6 py-4">
                <div class="text-sm font-semibold text-slate-900">Billing details</div>
                <div class="text-xs text-slate-500">Membership {{ $membership->membership_number }}</div>
            </div>
            <div class="p-6 text-slate-900">
                <form method="POST" action="{{ route('customer.membership.billing.update') }}" class="grid grid-cols-1 gap-3 rounded-xl border border-slate-200 p-4 sm:max-w-xl">
                    @csrf
                    <div>
                        <label class="text-xs font-medium text-slate-600">Provider</label>
                        <select name="billing_provider" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-hero-primary focus:ring-hero-primary">
                            <option value="">Select provider</option>
                            <option value="stripe" @selected($membership->billing_provider === 'stripe')>Stripe</option>
                            <option value="zoho" @selected($membership->billing_provider === 'zoho')>Zoho</option>
                            <option value="manual" @selected($membership->billing_provider === 'manual')>Manual / bank transfer</option>
                        </select>
                    </div>

                    <div>
                        <label class="text-xs font-medium text-slate-600">Customer reference</label>
                        <input name="billing_customer_id" value="{{ $membership->billing_customer_id }}" placeholder="Billing customer reference" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-hero-primary focus:ring-hero-primary">
                    </div>

                    <button class="rounded-lg bg-hero-primary px-3 py-2 text-sm font-semibold text-white transition hover:bg-hero-primary-hover">Update payment method</button>
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
