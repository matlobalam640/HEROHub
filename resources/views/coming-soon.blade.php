<x-portal-layout>
    <div class="space-y-4">
        @if (session('status'))
            <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                {{ session('status') }}
            </div>
        @endif

        @include('portal.partials.module-page-header', [
            'title' => str_replace('-', ' ', $page),
            'metrics' => $headerMetrics ?? [],
        ])

        <div class="grid gap-4">
            <div class="grid gap-3">
                @if(isset($preview['rows']))
                    <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-0 shadow-md shadow-slate-200/50 ring-1 ring-slate-100">
                        <div class="hero-panel-header border-b border-slate-100 bg-gradient-to-r from-slate-50 to-[color:var(--dashboard-gold-soft)] px-6 py-4">
                            <div class="text-sm font-semibold text-slate-800">{{ $page === 'memberships' ? 'Membership directory' : 'Sample records' }}</div>
                            @if($page === 'memberships')
                                <div class="text-xs text-slate-500">Latest {{ $preview['rows']->count() }} records · link opens detail</div>
                            @endif
                        </div>
                        <div class="hero-datatable px-2 pb-2">
                            <table class="js-datatable min-w-full divide-y divide-slate-200 text-left text-sm" data-dt-per-page="10">
                                <thead class="bg-slate-50/90 text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    @if($page === 'companies')
                                        <tr>
                                            <th class="px-6 py-3 w-16">#</th>
                                            <th class="px-6 py-3">Company</th>
                                            <th class="px-6 py-3">City</th>
                                            <th class="px-6 py-3">Phone</th>
                                        </tr>
                                    @elseif($page === 'partners')
                                        <tr>
                                            <th class="px-6 py-3 w-16">#</th>
                                            <th class="px-6 py-3">Partner</th>
                                            <th class="px-6 py-3">Commission</th>
                                            <th class="px-6 py-3">Status</th>
                                        </tr>
                                    @elseif($page === 'memberships')
                                        <tr>
                                            <th class="px-6 py-3 w-16">#</th>
                                            <th class="px-6 py-3">Membership</th>
                                            <th class="px-6 py-3 min-w-[10rem]">Primary member</th>
                                            <th class="px-6 py-3 min-w-[9rem]">Account</th>
                                            <th class="px-6 py-3 min-w-[8rem]">Plan</th>
                                            <th class="px-6 py-3">Status</th>
                                            <th class="px-6 py-3">Company</th>
                                            <th class="px-6 py-3">Partner</th>
                                            <th class="px-6 py-3 whitespace-nowrap">Coverage</th>
                                            <th class="px-6 py-3">Auto-renew</th>
                                        </tr>
                                    @elseif($page === 'customers')
                                        <tr>
                                            <th class="px-6 py-3 w-16">#</th>
                                            <th class="px-6 py-3">Name</th>
                                            <th class="px-6 py-3">Email</th>
                                            <th class="px-6 py-3">Membership</th>
                                            <th class="px-6 py-3">Status</th>
                                        </tr>
                                    @endif
                                </thead>
                                <tbody class="divide-y divide-slate-200">
                                    @foreach($preview['rows'] as $row)
                                        @if($page === 'companies')
                                            <tr class="hover:bg-slate-50/60">
                                                <td class="px-6 py-3 text-slate-500">{{ $loop->iteration }}</td>
                                                <td class="px-6 py-3 font-medium text-slate-800">{{ $row->name }}</td>
                                                <td class="px-6 py-3 text-slate-600">{{ $row->city ?? '—' }}</td>
                                                <td class="px-6 py-3 text-slate-600">{{ $row->phone ?? '—' }}</td>
                                            </tr>
                                        @elseif($page === 'partners')
                                            <tr class="hover:bg-slate-50/60">
                                                <td class="px-6 py-3 text-slate-500">{{ $loop->iteration }}</td>
                                                <td class="px-6 py-3 font-medium text-slate-800">{{ $row->name }}</td>
                                                <td class="px-6 py-3">{{ number_format((float) $row->commission_percent, 2) }}%</td>
                                                <td class="px-6 py-3">
                                                    <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold {{ $row->active ? 'bg-[color:var(--dashboard-secondary-soft)] text-[color:var(--dashboard-secondary-600)]' : 'bg-slate-100 text-slate-700' }}">
                                                        {{ $row->active ? 'Active' : 'Inactive' }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @elseif($page === 'memberships')
                                            @php
                                                $pm = $row->primaryMember;
                                                $primaryLabel = $pm
                                                    ? trim($pm->first_name.' '.$pm->last_name)
                                                    : null;
                                            @endphp
                                            <tr class="hover:bg-slate-50/60">
                                                <td class="px-6 py-3 text-slate-500">{{ $loop->iteration }}</td>
                                                <td class="px-6 py-3 font-mono text-xs font-semibold text-slate-800 dark:text-slate-200">
                                                    <a href="{{ route('portal.membership.show', $row) }}" class="text-hero-primary hover:underline">{{ $row->membership_number }}</a>
                                                </td>
                                                <td class="px-6 py-3 text-slate-800 dark:text-slate-200">
                                                    @if($primaryLabel)
                                                        <span class="font-medium">{{ $primaryLabel }}</span>
                                                        @if($pm->email)
                                                            <div class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">{{ $pm->email }}</div>
                                                        @endif
                                                    @else
                                                        <span class="text-slate-400">—</span>
                                                    @endif
                                                </td>
                                                <td class="px-6 py-3 text-slate-600 dark:text-slate-300">
                                                    @if($row->accountUser)
                                                        <span class="font-medium text-slate-800 dark:text-slate-200">{{ $row->accountUser->name }}</span>
                                                        <div class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">{{ $row->accountUser->email }}</div>
                                                    @else
                                                        <span class="text-slate-400">—</span>
                                                    @endif
                                                </td>
                                                <td class="px-6 py-3 text-slate-700 dark:text-slate-300">{{ $row->plan?->name ?? '—' }}</td>
                                                <td class="px-6 py-3">
                                                    @include('portal.partials.membership-status-badge', ['status' => $row->status])
                                                </td>
                                                <td class="px-6 py-3 text-slate-600 dark:text-slate-400">{{ $row->company?->name ?? '—' }}</td>
                                                <td class="px-6 py-3 text-slate-600 dark:text-slate-400">{{ $row->partner?->name ?? '—' }}</td>
                                                <td class="px-6 py-3 whitespace-nowrap text-slate-600 dark:text-slate-400">
                                                    {{ $row->coverage_starts_on?->format('M j, Y') ?? '—' }}
                                                    <span class="text-slate-400">–</span>
                                                    {{ $row->coverage_ends_on?->format('M j, Y') ?? '—' }}
                                                </td>
                                                <td class="px-6 py-3">
                                                    @if($row->auto_renew)
                                                        <span class="inline-flex rounded-full bg-[color:var(--dashboard-secondary-soft)] px-2 py-0.5 text-xs font-semibold text-[color:var(--dashboard-secondary-600)]">On</span>
                                                    @else
                                                        <span class="inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-600 dark:bg-slate-700/60 dark:text-slate-300">Off</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @elseif($page === 'customers')
                                            @php
                                                $m = $row->latestMembership;
                                            @endphp
                                            <tr class="hover:bg-slate-50/60">
                                                <td class="px-6 py-3 text-slate-500">{{ $loop->iteration }}</td>
                                                <td class="px-6 py-3 font-medium text-slate-800">{{ $row->name }}</td>
                                                <td class="px-6 py-3 text-slate-600">{{ $row->email }}</td>
                                                <td class="px-6 py-3">
                                                    @if($m)
                                                        <a href="{{ route('portal.membership.show', $m) }}" class="font-mono text-xs font-semibold text-hero-primary hover:underline">
                                                            {{ $m->membership_number }}
                                                        </a>
                                                        @if($m->plan)
                                                            <div class="mt-0.5 text-xs text-slate-500">{{ $m->plan->name }}</div>
                                                        @endif
                                                    @else
                                                        <span class="text-slate-400">—</span>
                                                    @endif
                                                </td>
                                                <td class="px-6 py-3">
                                                    @if($m)
                                                        @include('portal.partials.membership-status-badge', ['status' => $m->status])
                                                    @else
                                                        <span class="inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-600 dark:bg-slate-700/60 dark:text-slate-300">None</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @elseif(isset($preview['stats']))
                    <div class="grid gap-4 sm:grid-cols-3">
                        @foreach($preview['stats'] as $stat)
                            <div class="dashboard-stat-card">
                                <div class="text-xs font-medium uppercase tracking-wide text-slate-500">{{ $stat['label'] }}</div>
                                <div class="mt-2 text-2xl font-semibold text-slate-900">{{ $stat['value'] }}</div>
                            </div>
                        @endforeach
                    </div>
                @elseif(isset($preview['items']))
                    @if($page === 'settings')
                        <div class="grid w-full max-w-none grid-cols-1 gap-6 lg:grid-cols-12 lg:items-start lg:gap-8">
                            <div class="space-y-6 lg:col-span-5 xl:col-span-4">
                                <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-md shadow-slate-200/50 ring-1 ring-slate-100">
                                    <div class="hero-panel-header border-b border-slate-100 bg-gradient-to-r from-slate-50 to-[color:var(--dashboard-gold-soft)] px-6 py-4">
                                        <div class="text-sm font-semibold text-slate-800">Environment</div>
                                        <div class="text-xs text-slate-500">Read-only preview</div>
                                    </div>
                                    <dl class="divide-y divide-slate-100">
                                        @foreach($preview['items'] as $item)
                                            <div class="flex flex-wrap items-center justify-between gap-2 px-6 py-3">
                                                <dt class="text-sm text-slate-500">{{ $item['label'] }}</dt>
                                                <dd class="max-w-[55%] text-right text-sm font-medium text-slate-600 sm:max-w-none">{{ $item['value'] }}</dd>
                                            </div>
                                        @endforeach
                                    </dl>
                                </div>

                                <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-md shadow-slate-200/50 ring-1 ring-slate-100">
                                    <div class="hero-panel-header border-b border-slate-100 bg-gradient-to-r from-slate-50 to-[color:var(--dashboard-gold-soft)] px-6 py-4">
                                        <div class="text-sm font-semibold text-slate-800">{{ __('Profile & account') }}</div>
                                        <div class="text-xs text-slate-500">{{ __('Name and email on your profile page.') }}</div>
                                    </div>
                                    <div class="flex flex-col gap-4 p-6 sm:p-8">
                                        <p class="text-sm leading-relaxed text-slate-600">
                                            {{ __('Open the full profile page to update your display name and email.') }}
                                        </p>
                                        <a href="{{ route('profile.edit') }}"
                                           class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-hero-primary hover:text-hero-primary sm:w-auto">
                                            <i class="fa-solid fa-user-pen" aria-hidden="true"></i>
                                            {{ __('Open profile') }}
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <div class="lg:col-span-7 xl:col-span-8">
                                <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-md shadow-slate-200/50 ring-1 ring-slate-100">
                                    <div class="hero-panel-header border-b border-slate-100 bg-gradient-to-r from-slate-50 to-[color:var(--dashboard-gold-soft)] px-6 py-4">
                                        <div class="text-sm font-semibold text-slate-800">{{ __('Password') }}</div>
                                        <div class="text-xs text-slate-500">{{ __('Change the password you use to sign in to this portal.') }}</div>
                                    </div>
                                    <div class="p-6 text-slate-900 sm:p-8">
                                        @include('profile.partials.update-password-form', ['hideHeader' => true])
                                    </div>
                                </div>
                            </div>

                            @isset($preview['adminUsers'])
                                <div class="lg:col-span-12">
                                    @include('portal.partials.admin-user-accounts', ['users' => $preview['adminUsers']])
                                </div>
                            @endisset
                        </div>
                    @else
                        <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-md shadow-slate-200/50 ring-1 ring-slate-100">
                            <div class="hero-panel-header border-b border-slate-100 bg-gradient-to-r from-slate-50 to-[color:var(--dashboard-gold-soft)] px-6 py-4">
                                <div class="text-sm font-semibold text-slate-800">Environment</div>
                                <div class="text-xs text-slate-500">Read-only preview</div>
                            </div>
                            <dl class="divide-y divide-slate-100">
                                @foreach($preview['items'] as $item)
                                    <div class="flex flex-wrap items-center justify-between gap-2 px-6 py-3">
                                        <dt class="text-sm text-slate-500">{{ $item['label'] }}</dt>
                                        <dd class="text-sm font-medium text-slate-600">{{ $item['value'] }}</dd>
                                    </div>
                                @endforeach
                            </dl>
                        </div>
                    @endif
                @else
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <p class="text-sm text-slate-600">No preview table for this module yet.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-portal-layout>
