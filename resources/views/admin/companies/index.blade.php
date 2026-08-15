<x-portal-layout>
    <div class="space-y-6">
        <div class="hero-dispatch-hero">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <div class="hero-dispatch-hero__eyebrow">Admin · Operations</div>
                    <h1 class="hero-dispatch-hero__title">Companies</h1>
                    <p class="hero-dispatch-hero__lead">Business and corporate accounts created via migration or the company portal. Each company has an HR owner, default enrollment plan, and employee memberships.</p>
                </div>
                <div class="hidden shrink-0 sm:block" aria-hidden="true">
                    <span class="hero-dispatch-hero__icon">
                        <i class="fa-solid fa-building-columns" aria-hidden="true"></i>
                    </span>
                </div>
            </div>
        </div>

        <div class="hero-portal-panel overflow-hidden">
            <div class="hero-panel-header flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-6 py-4">
                <div class="text-sm font-semibold text-slate-900">Company directory</div>
                <form method="GET" action="{{ route('admin.companies.index') }}" class="flex gap-2">
                    <input type="search" name="q" value="{{ $search }}" placeholder="Search name, billing email, owner…"
                           class="rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-hero-primary focus:ring-hero-primary">
                    <button type="submit" class="rounded-xl bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-200">Search</button>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">
                        <tr>
                            <th class="px-4 py-3">Company</th>
                            <th class="px-4 py-3">HR owner</th>
                            <th class="px-4 py-3">Default plan</th>
                            <th class="px-4 py-3">Employees</th>
                            <th class="px-4 py-3">Billing email</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($companies as $company)
                            <tr>
                                <td class="px-4 py-3">
                                    <div class="font-semibold text-slate-900">{{ $company->name }}</div>
                                    @if ($company->city || $company->country)
                                        <div class="text-xs text-slate-500">{{ collect([$company->city, $company->country])->filter()->implode(', ') }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @if ($company->ownerUser)
                                        <div>{{ $company->ownerUser->name }}</div>
                                        <div class="text-xs text-slate-500">{{ $company->ownerUser->email }}</div>
                                    @else
                                        <span class="text-xs text-amber-700">No owner linked</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 font-mono text-xs">
                                    {{ $company->defaultPlan?->code ?? '—' }}
                                </td>
                                <td class="px-4 py-3">
                                    <span class="font-semibold text-slate-900">{{ $company->memberships_count }}</span>
                                    <span class="text-xs text-slate-500">total</span>
                                    @if ($company->active_memberships_count > 0)
                                        <span class="ml-2 text-xs text-emerald-700">{{ $company->active_memberships_count }} active</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-xs text-slate-600">{{ $company->billing_email ?: '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-slate-600">
                                    No companies yet. Import a CSV with <span class="font-mono">record_type=b2b_company</span> rows under
                                    <a href="{{ route('admin.migration.index') }}" class="font-semibold text-hero-primary hover:underline">Migration</a>.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($companies->hasPages())
                <div class="border-t border-slate-100 px-6 py-4">
                    {{ $companies->links() }}
                </div>
            @endif
        </div>

        <div class="hero-portal-panel overflow-hidden">
            <div class="hero-panel-header border-b border-slate-100 px-6 py-4">
                <div class="text-sm font-semibold text-slate-900">How companies are added</div>
            </div>
            <ul class="list-disc space-y-2 p-6 pl-10 text-sm text-slate-700">
                <li><strong>CSV migration</strong> — use <span class="font-mono">record_type=b2b_company</span> and <span class="font-mono">b2b_employee</span> rows under <a href="{{ route('admin.migration.index') }}" class="font-semibold text-hero-primary hover:underline">Migration</a>.</li>
                <li><strong>Walk-in enroll</strong> — retail (B2C) members only; business accounts are not created from the office checkout flow.</li>
                <li><strong>AWS subscriptions</strong> — sync retail memberships only; B2B is not created from the payment gateway.</li>
            </ul>
        </div>
    </div>
</x-portal-layout>
