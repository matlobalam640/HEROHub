<x-portal-layout>
    <div class="w-full max-w-none space-y-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <div class="text-sm font-medium text-hero-primary">Partner / Reseller</div>
                <h1 class="font-display mt-1 text-2xl font-semibold tracking-tight text-slate-900">Sales &amp; membership status</h1>
                <p class="mt-1 text-sm text-slate-600">Every enrollment creates a sale row with plan, amount, commission rate, and commission owed.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('partner.portal') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 shadow-sm hover:border-hero-primary">← Portal</a>
                <a href="{{ route('partner.enroll.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-hero-primary px-3 py-2 text-xs font-semibold text-white shadow-sm hover:bg-hero-primary-hover">Enroll</a>
            </div>
        </div>

        @if (session('status'))
            <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('status') }}</div>
        @endif

        <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-4">
                <h2 class="text-sm font-semibold text-slate-900">All sales ({{ $partner->name }})</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Partner</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Sold</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Membership #</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Plan</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Member</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Status</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-600">Sale amount</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-600">Commission %</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-600">Commission owed</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($sales as $sale)
                            @php
                                $m = $sale->membership;
                                $primary = $m?->members->firstWhere('is_primary', true) ?? $m?->members->first();
                            @endphp
                            <tr>
                                <td class="px-4 py-3 text-slate-800">{{ $partner->name }}</td>
                                <td class="whitespace-nowrap px-4 py-3 text-slate-700">{{ $sale->sold_at?->timezone(config('app.timezone'))->format('M j, Y g:i A') }}</td>
                                <td class="whitespace-nowrap px-4 py-3 font-mono text-xs text-slate-700">{{ $m?->membership_number ?? '—' }}</td>
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
                                <td class="whitespace-nowrap px-4 py-3 text-right text-slate-700">{{ number_format((float) $sale->commission_percent, 2) }}%</td>
                                <td class="whitespace-nowrap px-4 py-3 text-right font-medium text-hero-primary">${{ number_format((float) $sale->commission_amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-4 py-8 text-center text-slate-500">No sales recorded yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($sales->hasPages())
                <div class="border-t border-slate-100 px-5 py-4">
                    {{ $sales->links() }}
                </div>
            @endif
        </div>
    </div>
</x-portal-layout>
