<x-portal-layout>
    <div class="space-y-8">
        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-900">
                {{ session('status') }}
            </div>
        @endif
        <div>
            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Operations</div>
            <div class="mt-1 flex flex-wrap items-center justify-between gap-4">
                <h1 class="font-display text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">Small Business Plans</h1>
                <div class="flex flex-wrap items-center gap-4">
                    @include('plans.partials.admin-add-plan-link', ['from' => 'small-business'])
                    <a href="{{ route('dashboard') }}" class="text-sm font-semibold text-[color:var(--dashboard-secondary-600)] hover:text-hero-primary">← Back to overview</a>
                </div>
            </div>
            <p class="mt-2 max-w-3xl text-sm text-slate-600 dark:text-slate-400">
                Please note: All listed prices are in U.S. Dollars and are subject to a 10% TCA tax.
            </p>
        </div>

        @include('plans.partials.small-business-catalog', ['sections' => $smallBusinessCatalogSections])
    </div>
</x-portal-layout>
