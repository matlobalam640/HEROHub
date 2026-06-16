<x-portal-layout>
    <div class="w-full max-w-none space-y-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <div class="text-sm font-medium text-hero-primary">Partner / Reseller</div>
                <h1 class="font-display mt-1 text-2xl font-semibold tracking-tight text-slate-900">Commission report</h1>
                <p class="mt-1 text-sm text-slate-600">Totals and monthly breakdown for <span class="font-semibold text-slate-800">{{ $partner->name }}</span>. Default commission rate on the partner record is {{ number_format((float) $partner->commission_percent, 2) }}%; each sale stores the rate and amount that applied.</p>
            </div>
            <a href="{{ route('partner.portal') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 shadow-sm hover:border-hero-primary">← Portal</a>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm">
                <div class="text-xs font-medium uppercase tracking-wide text-slate-500">Sales count</div>
                <div class="mt-1 text-2xl font-semibold text-slate-900">{{ number_format($totals->sale_count) }}</div>
            </div>
            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm">
                <div class="text-xs font-medium uppercase tracking-wide text-slate-500">Gross sales</div>
                <div class="mt-1 text-2xl font-semibold text-slate-900">${{ number_format($totals->sale_sum, 2) }}</div>
            </div>
            <div class="rounded-2xl border border-slate-200/90 bg-hero-primary-soft p-5 shadow-sm ring-1 ring-slate-200/60">
                <div class="text-xs font-medium uppercase tracking-wide text-hero-primary">Commission owed (total)</div>
                <div class="mt-1 text-2xl font-semibold text-hero-primary">${{ number_format($totals->commission_sum, 2) }}</div>
            </div>
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-4">
                <h2 class="text-sm font-semibold text-slate-900">By month (sale date)</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Month</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-600">Sales</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-600">Sale amount</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-600">Commission owed</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($byMonth as $row)
                            <tr>
                                <td class="px-4 py-3 font-medium text-slate-800">{{ $row->ym }}</td>
                                <td class="px-4 py-3 text-right text-slate-700">{{ number_format($row->sale_count) }}</td>
                                <td class="px-4 py-3 text-right text-slate-800">${{ number_format($row->sale_sum, 2) }}</td>
                                <td class="px-4 py-3 text-right font-medium text-hero-primary">${{ number_format($row->commission_sum, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-slate-500">No commission data yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-portal-layout>
