@php
    $intervalLabel = $interval === 'yearly' ? 'Annual' : 'Monthly';
@endphp

<x-portal-layout>
    <div class="w-full max-w-none space-y-6">
        <div>
            @include('customer.membership.partials.portal-eyebrow')
            <h1 class="font-display mt-1 text-2xl font-semibold tracking-tight text-slate-900">Checkout</h1>
            <p class="mt-1 text-sm text-slate-600">Review your new plan and amount. You will be redirected to Stripe to complete payment securely.</p>
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-md shadow-slate-200/50 ring-1 ring-slate-100">
            <div class="hero-panel-header border-b border-slate-100 bg-gradient-to-r from-slate-50 to-[color:var(--dashboard-gold-soft)] px-6 py-4">
                <div class="text-sm font-semibold text-slate-900">Order summary</div>
                <div class="text-xs text-slate-500">Current plan: {{ $membership->plan?->name ?? '—' }}</div>
            </div>
            <div class="space-y-4 p-6 text-slate-900">
                <dl class="grid grid-cols-1 gap-3 text-sm sm:grid-cols-2">
                    <div class="rounded-xl border border-slate-200 bg-slate-50/60 px-4 py-3">
                        <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">New plan</dt>
                        <dd class="mt-1 font-semibold text-slate-900">{{ $plan->name }}</dd>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-slate-50/60 px-4 py-3">
                        <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Billing</dt>
                        <dd class="mt-1 font-semibold text-slate-900">{{ $intervalLabel }}</dd>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-slate-50/60 px-4 py-3 sm:col-span-2">
                        <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Due today</dt>
                        <dd class="mt-1 text-lg font-bold tabular-nums text-hero-primary">${{ number_format($usdAmount, 2) }} USD</dd>
                    </div>
                </dl>

                <form method="POST" action="{{ route('customer.membership.plan.stripe.start') }}" class="flex flex-wrap items-center gap-3">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">
                    <button type="submit" class="rounded-lg bg-hero-primary px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-hero-primary-hover">
                        Pay with Stripe
                    </button>
                    <a href="{{ route('customer.membership.plan') }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:border-slate-400">
                        Cancel
                    </a>
                </form>
            </div>
        </div>
    </div>
</x-portal-layout>
