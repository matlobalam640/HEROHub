<x-portal-layout>
    <div class="w-full max-w-none space-y-6">
        <div>
            <div class="text-sm font-medium text-hero-primary">Partner / Reseller</div>
            <h1 class="font-display mt-1 text-2xl font-semibold tracking-tight text-slate-900">Partner portal</h1>
            <p class="mt-1 text-sm text-slate-600">Enroll members, track sales, and review commissions for <span class="font-semibold text-slate-800">{{ $partner->name }}</span>.</p>
        </div>

        @if (session('status'))
            <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                {{ session('status') }}
            </div>
        @endif

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm">
                <div class="text-xs font-medium uppercase tracking-wide text-slate-500">Memberships sold</div>
                <div class="mt-1 text-2xl font-semibold text-slate-900">{{ number_format($stats['sales_count']) }}</div>
            </div>
            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm">
                <div class="text-xs font-medium uppercase tracking-wide text-slate-500">Active (your enrollments)</div>
                <div class="mt-1 text-2xl font-semibold text-slate-900">{{ number_format($stats['active_memberships']) }}</div>
            </div>
            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm">
                <div class="text-xs font-medium uppercase tracking-wide text-slate-500">Gross sale amount</div>
                <div class="mt-1 text-2xl font-semibold text-slate-900">${{ number_format($stats['sale_total'], 2) }}</div>
            </div>
            <div class="rounded-2xl border border-slate-200/90 bg-hero-primary-soft p-5 shadow-sm ring-1 ring-slate-200/60">
                <div class="text-xs font-medium uppercase tracking-wide text-hero-primary">Commission owed (total)</div>
                <div class="mt-1 text-2xl font-semibold text-hero-primary">${{ number_format($stats['commission_total'], 2) }}</div>
                <div class="mt-2 text-xs text-slate-600">Rate: {{ number_format((float) $partner->commission_percent, 2) }}% (per sale)</div>
            </div>
        </div>

        <div class="flex flex-wrap gap-3">
            <a href="{{ route('partner.enroll.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-hero-primary px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-hero-primary-hover">
                <i class="fa-solid fa-user-plus" aria-hidden="true"></i>
                Enroll new member
            </a>
            <a href="{{ route('partner.sales.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm hover:border-hero-primary hover:text-hero-primary">
                <i class="fa-solid fa-receipt" aria-hidden="true"></i>
                Sales &amp; status
            </a>
            <a href="{{ route('partner.commissions') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm hover:border-hero-primary hover:text-hero-primary">
                <i class="fa-solid fa-chart-pie" aria-hidden="true"></i>
                Commission report
            </a>
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-4">
                <h2 class="text-sm font-semibold text-slate-900">Recent sales</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Sold</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Plan</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Member</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Status</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-600">Sale</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-600">Commission</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($recentSales as $sale)
                            @php
                                $m = $sale->membership;
                                $primary = $m?->members->firstWhere('is_primary', true) ?? $m?->members->first();
                            @endphp
                            <tr>
                                <td class="whitespace-nowrap px-4 py-3 text-slate-700">{{ $sale->sold_at?->timezone(config('app.timezone'))->format('M j, Y') }}</td>
                                <td class="px-4 py-3 text-slate-800">{{ $sale->plan?->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-slate-700">
                                    @if($primary)
                                        {{ $primary->first_name }} {{ $primary->last_name }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @if($m)
                                        <span class="inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-800">{{ $m->status }}</span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-right text-slate-800">${{ number_format((float) $sale->sale_amount, 2) }}</td>
                                <td class="whitespace-nowrap px-4 py-3 text-right font-medium text-hero-primary">${{ number_format((float) $sale->commission_amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-slate-500">No sales yet. Enroll a member to create your first sale record.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-portal-layout>
