<x-portal-layout>
    <div class="space-y-4">
        @include('portal.partials.module-page-header', [
            'title' => $membership->membership_number,
            'eyebrow' => 'Membership',
            'capitalizeTitle' => false,
            'titleClass' => '!font-mono !font-semibold',
            'metrics' => [
                ['label' => 'Status', 'value' => ucfirst((string) $membership->status)],
                ['label' => 'Plan', 'value' => \Illuminate\Support\Str::limit($membership->plan?->name ?? '—', 24)],
                ['label' => 'Coverage ends', 'value' => optional($membership->coverage_ends_on)->toFormattedDateString() ?? '—'],
            ],
            'toolbarLink' => [
                'href' => route('portal.coming-soon', ['page' => 'customers']),
                'label' => 'Back to customers',
                'icon' => 'fa-solid fa-arrow-left',
            ],
        ])
        @if($membership->accountUser)
            <p class="text-xs text-slate-600 dark:text-slate-400">
                Account holder:
                <span class="font-medium text-slate-800 dark:text-slate-200">{{ $membership->accountUser->name }}</span>
                <span class="text-slate-400"> · </span>
                {{ $membership->accountUser->email }}
            </p>
        @endif

        <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-md shadow-slate-200/50 ring-1 ring-slate-100 dark:border-slate-600/80 dark:bg-[#2f3349] dark:shadow-none dark:ring-slate-600/50">
            <div class="hero-panel-header border-b border-slate-100 px-6 py-4 dark:border-slate-600">
                <div class="text-sm font-semibold text-slate-800 dark:text-slate-100">Details</div>
            </div>
            <dl class="divide-y divide-slate-100 dark:divide-slate-600">
                <div class="flex flex-wrap items-center justify-between gap-2 px-6 py-3">
                    <dt class="text-sm text-slate-500 dark:text-slate-400">Status</dt>
                    <dd class="text-sm font-medium text-slate-800 dark:text-slate-200">
                        @include('portal.partials.membership-status-badge', ['status' => $membership->status])
                    </dd>
                </div>
                <div class="flex flex-wrap items-center justify-between gap-2 px-6 py-3">
                    <dt class="text-sm text-slate-500 dark:text-slate-400">Plan</dt>
                    <dd class="text-sm font-medium text-slate-800 dark:text-slate-200">{{ $membership->plan?->name ?? '—' }}</dd>
                </div>
                <div class="flex flex-wrap items-center justify-between gap-2 px-6 py-3">
                    <dt class="text-sm text-slate-500 dark:text-slate-400">Company</dt>
                    <dd class="text-sm font-medium text-slate-800 dark:text-slate-200">{{ $membership->company?->name ?? '—' }}</dd>
                </div>
                <div class="flex flex-wrap items-center justify-between gap-2 px-6 py-3">
                    <dt class="text-sm text-slate-500 dark:text-slate-400">Coverage</dt>
                    <dd class="text-sm font-medium text-slate-800 dark:text-slate-200">
                        {{ optional($membership->coverage_starts_on)->toFormattedDateString() ?? '—' }}
                        –
                        {{ optional($membership->coverage_ends_on)->toFormattedDateString() ?? '—' }}
                    </dd>
                </div>
            </dl>
        </div>
    </div>
</x-portal-layout>
