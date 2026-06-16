<x-portal-layout>
    <div class="space-y-6">
        <div class="rounded-2xl border border-red-100 bg-gradient-to-r from-red-50/90 to-amber-50/80 px-5 py-4 shadow-sm ring-1 ring-red-100/80">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-red-800/90">Dispatch · Emergency verification</div>
                    <h1 class="font-display mt-1 text-2xl font-semibold tracking-tight text-slate-900">Coverage verification</h1>
                    <p class="mt-1 max-w-3xl text-sm text-slate-700">Search the live membership database for instant confirmation of coverage during emergencies. Results reflect current plan and status in this system.</p>
                </div>
                <div class="hidden shrink-0 sm:block" aria-hidden="true">
                    <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-white/90 text-xl text-red-600 shadow-sm ring-1 ring-red-100">
                        <i class="fa-solid fa-bolt" aria-hidden="true"></i>
                    </span>
                </div>
            </div>
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-md shadow-slate-200/50 ring-1 ring-slate-100">
            <div class="hero-panel-header border-b border-slate-100 bg-gradient-to-r from-slate-50 to-[color:var(--dashboard-gold-soft)] px-6 py-4">
                <div class="text-sm font-semibold text-slate-900">Member lookup</div>
                <p class="mt-1 text-xs text-slate-600">Search by <strong>member name</strong> (given, family, or full name), <strong>membership ID</strong>, <strong>phone</strong>, <strong>company name</strong>, or a <strong>household / family member</strong> (spouse, child, visitor) on the plan.</p>
            </div>
            <div class="p-6">
                <form method="GET" action="{{ route('dispatch.verification') }}" class="flex flex-col gap-3 sm:flex-row sm:items-end">
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-slate-700" for="q">Search</label>
                        <input
                            id="q"
                            name="q"
                            value="{{ $q }}"
                            autocomplete="off"
                            class="mt-1 block w-full rounded-xl border-slate-200 shadow-sm focus:border-hero-primary focus:ring-hero-primary"
                            placeholder="e.g. Maria Garcia, HERO-PR-ABC123, 555-0100, Acme Corp…"
                        />
                    </div>
                    <button type="submit" class="inline-flex justify-center rounded-xl bg-hero-primary px-5 py-2.5 text-sm font-semibold text-white shadow-hero-cta transition hover:bg-hero-primary-hover focus:outline-none focus:ring-2 focus:ring-hero-primary/35 focus:ring-offset-2">
                        <span class="mr-2"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i></span>
                        Verify coverage
                    </button>
                </form>

                @if (! $hasSearched)
                    <div class="mt-6 rounded-xl border border-slate-200 bg-slate-50/80 px-4 py-5 text-sm text-slate-700">
                        <p class="font-medium text-slate-900">Enter a search term to load results</p>
                        <p class="mt-1">For fastest verification, use the membership number from the ID card, or the account holder’s phone number. You can also find a plan by searching a dependent’s name.</p>
                    </div>
                @else
                    <div class="mt-6 overflow-x-auto rounded-xl border border-slate-200/80">
                        <table class="min-w-full divide-y divide-slate-200 text-sm">
                            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">
                                <tr>
                                    <th class="px-4 py-3">Membership ID</th>
                                    <th class="px-4 py-3">Account holder</th>
                                    <th class="px-4 py-3">Phone</th>
                                    <th class="px-4 py-3">Membership type</th>
                                    <th class="px-4 py-3">Coverage level</th>
                                    <th class="px-4 py-3">Activation status</th>
                                    <th class="px-4 py-3">Start date</th>
                                    <th class="px-4 py-3">Expiration</th>
                                    <th class="px-4 py-3">Company</th>
                                    <th class="px-4 py-3">Household</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white text-slate-900">
                                @forelse($memberships as $membership)
                                    @php
                                        $primary = $membership->members->firstWhere('is_primary', true) ?? $membership->members->first();
                                        $plan = $membership->plan;
                                        $status = $membership->status;
                                        $statusClass = match ($status) {
                                            'active' => 'bg-emerald-50 text-emerald-800 ring-emerald-600/15',
                                            'inactive' => 'bg-amber-50 text-amber-900 ring-amber-600/15',
                                            'expired' => 'bg-slate-100 text-slate-700 ring-slate-500/15',
                                            'cancelled' => 'bg-red-50 text-red-800 ring-red-600/15',
                                            default => 'bg-slate-100 text-slate-700 ring-slate-500/15',
                                        };
                                        $deps = $membership->dependents;
                                        $householdParts = $deps->map(function ($d) {
                                            $rel = $d->relationship ? ' ('.$d->relationship.')' : '';

                                            return trim($d->first_name.' '.$d->last_name).$rel;
                                        })->filter()->take(4);
                                    @endphp
                                    <tr class="align-top">
                                        <td class="whitespace-nowrap px-4 py-3 font-mono text-xs text-slate-800">{{ $membership->membership_number }}</td>
                                        <td class="px-4 py-3">
                                            @if($primary)
                                                <span class="font-semibold text-slate-900">{{ $primary->first_name }} {{ $primary->last_name }}</span>
                                            @else
                                                <span class="text-slate-500">—</span>
                                            @endif
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3 text-slate-600">{{ $primary?->phone ?? '—' }}</td>
                                        <td class="px-4 py-3 text-slate-800">
                                            @if($plan)
                                                <span class="font-medium">{{ $plan->dispatchMembershipTypeLabel() }}</span>
                                                <div class="text-xs text-slate-500">{{ $plan->name }}</div>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-slate-800">
                                            {{ $plan ? $plan->dispatchCoverageLevelLabel() : '—' }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold capitalize ring-1 {{ $statusClass }}">
                                                {{ $status }}
                                            </span>
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3 text-slate-700">{{ $membership->coverage_starts_on?->format('M j, Y') ?? '—' }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 text-slate-700">{{ $membership->coverage_ends_on?->format('M j, Y') ?? '—' }}</td>
                                        <td class="max-w-[10rem] px-4 py-3 text-slate-700">{{ $membership->company?->name ?? '—' }}</td>
                                        <td class="max-w-[14rem] px-4 py-3 text-xs text-slate-600">
                                            @if($deps->isEmpty())
                                                —
                                            @else
                                                <span class="leading-relaxed">{{ $householdParts->join('; ') }}@if($deps->count() > 4) <span class="text-slate-400">(+{{ $deps->count() - 4 }} more)</span>@endif</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="px-4 py-10 text-center text-slate-600">No memberships match this search. Try membership number, another spelling, or company name.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($memberships->hasPages())
                        <div class="mt-4 border-t border-slate-100 pt-4">
                            {{ $memberships->links() }}
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</x-portal-layout>
