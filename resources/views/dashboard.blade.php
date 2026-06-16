@php
    $membershipsOther = max(0, $stats['memberships_total'] - $stats['memberships_active']);
@endphp

<x-portal-layout>
    <div class="hero-dashboard-shell space-y-8">
        @include('portal.partials.module-page-header', [
            'title' => 'Portal overview',
            'eyebrow' => 'Overview',
            'metrics' => [
                ['label' => 'Customers', 'value' => $stats['customers']],
                ['label' => 'Memberships', 'value' => $stats['memberships_total']],
                ['label' => 'Active', 'value' => $stats['memberships_active']],
                ['label' => 'Partner sales', 'value' => $stats['partner_sales']],
            ],
        ])

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="dashboard-stat-card">
                <div class="flex items-start justify-between gap-2">
                    <div class="dashboard-stat-icon--vuexy-primary flex h-11 w-11 shrink-0 items-center justify-center rounded-full text-lg">
                        <i class="fa-solid fa-users" aria-hidden="true"></i>
                    </div>
                    <button type="button" class="rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600" aria-label="Card options">
                        <i class="fa-solid fa-ellipsis-vertical" aria-hidden="true"></i>
                    </button>
                </div>
                <div class="mt-4 text-sm font-medium text-slate-500">Customers</div>
                <div class="mt-1 font-display text-3xl font-bold tracking-tight text-slate-900">{{ number_format($stats['customers']) }}</div>
                <div class="mt-2 text-xs text-slate-500">Users with the <span class="font-semibold text-slate-700">customer</span> role</div>
            </div>

            <div class="dashboard-stat-card">
                <div class="flex items-start justify-between gap-2">
                    <div class="dashboard-stat-icon--vuexy-success flex h-11 w-11 shrink-0 items-center justify-center rounded-full text-lg">
                        <i class="fa-solid fa-id-card-clip" aria-hidden="true"></i>
                    </div>
                    <button type="button" class="rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600" aria-label="Card options">
                        <i class="fa-solid fa-ellipsis-vertical" aria-hidden="true"></i>
                    </button>
                </div>
                <div class="mt-4 text-sm font-medium text-slate-500">Memberships</div>
                <div class="mt-1 font-display text-3xl font-bold tracking-tight text-slate-900">{{ number_format($stats['memberships_total']) }}</div>
                <div class="mt-2 text-xs text-slate-500">
                    <span class="font-semibold text-[color:var(--vuexy-success)]">Active: {{ number_format($stats['memberships_active']) }}</span>
                    <span class="text-slate-400"> | </span>
                    <span class="font-semibold text-[color:var(--vuexy-danger)]">Other: {{ number_format($membershipsOther) }}</span>
                </div>
            </div>

            <div class="dashboard-stat-card">
                <div class="flex items-start justify-between gap-2">
                    <div class="dashboard-stat-icon--vuexy-info flex h-11 w-11 shrink-0 items-center justify-center rounded-full text-lg">
                        <i class="fa-solid fa-building-columns" aria-hidden="true"></i>
                    </div>
                    <button type="button" class="rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600" aria-label="Card options">
                        <i class="fa-solid fa-ellipsis-vertical" aria-hidden="true"></i>
                    </button>
                </div>
                <div class="mt-4 text-sm font-medium text-slate-500">Companies</div>
                <div class="mt-1 font-display text-3xl font-bold tracking-tight text-slate-900">{{ number_format($stats['companies']) }}</div>
                <div class="mt-2 text-xs text-slate-500">Organizations on file</div>
            </div>

            <div class="dashboard-stat-card">
                <div class="flex items-start justify-between gap-2">
                    <div class="dashboard-stat-icon--vuexy-warning flex h-11 w-11 shrink-0 items-center justify-center rounded-full text-lg">
                        <i class="fa-solid fa-sack-dollar" aria-hidden="true"></i>
                    </div>
                    <button type="button" class="rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600" aria-label="Card options">
                        <i class="fa-solid fa-ellipsis-vertical" aria-hidden="true"></i>
                    </button>
                </div>
                <div class="mt-4 text-sm font-medium text-slate-500">Partner sales</div>
                <div class="mt-1 font-display text-3xl font-bold tracking-tight text-slate-900">{{ number_format($stats['partner_sales']) }}</div>
                <div class="mt-2 text-xs text-slate-500"><span class="font-semibold text-slate-700">{{ number_format($stats['partners']) }}</span> partners</div>
            </div>
        </div>

        <div class="space-y-6">
                <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
                    <div class="hero-dashboard-panel overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-[0_4px_24px_-6px_rgba(15,23,42,0.08)] xl:col-span-2">
                        <div class="dashboard-card-header flex items-center justify-between">
                            <div>
                                <div class="dashboard-card-header__title">Membership growth</div>
                                <div class="dashboard-card-header__sub">Last 12 months</div>
                            </div>
                        </div>
                        <div class="p-6">
                            <div class="h-64">
                                <canvas id="membershipGrowthChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <div class="hero-dashboard-panel overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-[0_4px_24px_-6px_rgba(15,23,42,0.08)]">
                        <div class="dashboard-card-header">
                            <div class="dashboard-card-header__title">Membership status</div>
                            <div class="dashboard-card-header__sub">Distribution</div>
                        </div>
                        <div class="p-6">
                            @if(count($membershipStatusChart['labels']))
                                <div class="mx-auto h-52 w-52 max-w-full">
                                    <canvas id="membershipStatusChart"></canvas>
                                </div>
                            @else
                                <p class="py-8 text-center text-sm text-slate-500">No memberships yet.</p>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="hero-dashboard-panel overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-[0_4px_24px_-6px_rgba(15,23,42,0.08)]">
                    <div class="dashboard-card-header">
                        <div class="dashboard-card-header__title">Partner sales volume</div>
                        <div class="dashboard-card-header__sub">Sales recorded per month (last 6 months)</div>
                    </div>
                    <div class="p-6">
                        <div class="h-56">
                            <canvas id="partnerSalesChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="hero-dashboard-panel overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-[0_4px_24px_-6px_rgba(15,23,42,0.08)]">
                    <div class="dashboard-card-header">
                        <div class="dashboard-card-header__title">Recent memberships</div>
                        <div class="dashboard-card-header__sub">Latest records — sort, search, and paginate</div>
                    </div>
                    <div class="p-2">
                        <div class="hero-datatable">
                            <table class="js-datatable w-full text-sm" data-dt-per-page="8">
                                <thead class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    <tr>
                                        <th class="px-2 py-2">Number</th>
                                        <th class="px-2 py-2">Member</th>
                                        <th class="px-2 py-2">Plan</th>
                                        <th class="px-2 py-2">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentMemberships as $m)
                                        @php
                                            $primary = $m->members->firstWhere('is_primary', true) ?? $m->members->first();
                                        @endphp
                                        <tr>
                                            <td class="px-2 py-2 font-mono text-xs font-semibold text-slate-800">{{ $m->membership_number }}</td>
                                            <td class="px-2 py-2 text-slate-700">{{ $primary ? ($primary->first_name.' '.$primary->last_name) : '—' }}</td>
                                            <td class="px-2 py-2 text-slate-600">{{ $m->plan?->name ?? '—' }}</td>
                                            <td class="px-2 py-2">
                                                <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-semibold
                                                    @class([
                                                        'bg-[rgba(40,199,111,0.12)] text-[color:var(--vuexy-success)]' => $m->status === 'active',
                                                        'bg-slate-100 text-slate-600' => $m->status !== 'active',
                                                    ])">
                                                    {{ ucfirst($m->status) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-2 py-6 text-center text-slate-500">No memberships yet.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="hero-dashboard-panel overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-[0_4px_24px_-6px_rgba(15,23,42,0.08)]">
                    <div class="dashboard-card-header">
                        <div class="dashboard-card-header__title">Recent activity</div>
                        <div class="dashboard-card-header__sub">New memberships and partner sales</div>
                    </div>
                    <div class="hero-datatable px-2 pb-2">
                        <table class="js-datatable min-w-full divide-y divide-slate-200 text-left text-sm" data-dt-per-page="8">
                            <thead class="bg-slate-50/80 text-xs font-semibold uppercase tracking-wide text-slate-500">
                                <tr>
                                    <th class="px-4 py-3">Type</th>
                                    <th class="px-4 py-3">Summary</th>
                                    <th class="px-4 py-3">Detail</th>
                                    <th class="px-4 py-3">When</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200">
                                @forelse($recentActivity as $row)
                                    <tr class="hover:bg-slate-50/60">
                                        <td class="px-4 py-3">
                                            <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold
                                                @class([
                                                    'bg-[rgba(115,103,240,0.12)] text-[color:var(--vuexy-primary)]' => $row['kind'] === 'membership',
                                                    'bg-[rgba(255,159,67,0.12)] text-[color:var(--vuexy-warning)]' => $row['kind'] === 'sale',
                                                ])">
                                                {{ $row['kind'] === 'membership' ? 'Membership' : 'Sale' }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 font-medium text-slate-800">{{ $row['title'] }}</td>
                                        <td class="px-4 py-3 text-slate-600">{{ $row['detail'] }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 text-slate-500">{{ $row['at']->timezone(config('app.timezone'))->format('M j, Y g:i a') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-8 text-center text-slate-500">No activity yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (!window.Chart) return;

            const root = document.documentElement;
            const vx = (token, fallback) => {
                const v = getComputedStyle(root).getPropertyValue(token).trim();
                return v || fallback;
            };
            /* Vuexy Bootstrap “Analytics” chart palette (see theme docs) */
            const V = {
                primary: vx('--vuexy-primary', '#7367f0'),
                success: vx('--vuexy-success', '#28c76f'),
                warning: vx('--vuexy-warning', '#ff9f43'),
                danger: vx('--vuexy-danger', '#ea5455'),
                info: vx('--vuexy-info', '#00cfe8'),
                secondary: vx('--vuexy-secondary', '#82868b'),
            };
            const vuexySeries = [V.primary, V.success, V.warning, V.danger, V.info, V.secondary];
            const vuexySeriesDark = ['#5e50ee', '#24b263', '#e68a2e', '#d64547', '#00b5cc', '#6c6f72'];

            const lineEl = document.getElementById('membershipGrowthChart');
            if (lineEl) {
                const payload = @json($membershipChart);
                const ctx = lineEl.getContext('2d');
                const h = lineEl.offsetHeight || 256;
                const lineFill = ctx.createLinearGradient(0, 0, 0, h);
                lineFill.addColorStop(0, 'rgba(115, 103, 240, 0.32)');
                lineFill.addColorStop(0.45, 'rgba(115, 103, 240, 0.14)');
                lineFill.addColorStop(1, 'rgba(115, 103, 240, 0.02)');
                new window.Chart(lineEl, {
                    type: 'line',
                    data: {
                        labels: payload.labels,
                        datasets: [{
                            label: 'New memberships',
                            data: payload.data,
                            borderColor: V.primary,
                            backgroundColor: lineFill,
                            fill: true,
                            tension: 0.35,
                            pointRadius: 3,
                            pointHoverRadius: 5,
                            pointBackgroundColor: V.info,
                            pointBorderColor: '#fff',
                            pointBorderWidth: 1.5,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: { mode: 'index', intersect: false },
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                ticks: { color: '#64748b', font: { size: 11 } }
                            },
                            y: {
                                beginAtZero: true,
                                grid: { color: 'rgba(148,163,184,0.25)' },
                                ticks: { color: '#64748b', font: { size: 11 }, precision: 0 }
                            }
                        }
                    }
                });
            }

            const doughnutEl = document.getElementById('membershipStatusChart');
            if (doughnutEl) {
                const statusPayload = @json($membershipStatusChart);
                new window.Chart(doughnutEl, {
                    type: 'doughnut',
                    data: {
                        labels: statusPayload.labels,
                        datasets: [{
                            data: statusPayload.data,
                            backgroundColor: statusPayload.labels.map((_, i) => vuexySeries[i % vuexySeries.length]),
                            borderWidth: 3,
                            borderColor: '#ffffff',
                            hoverBorderColor: '#ffffff',
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: { boxWidth: 12, font: { size: 11 } }
                            }
                        }
                    }
                });
            }

            const barEl = document.getElementById('partnerSalesChart');
            if (barEl) {
                const salesPayload = @json($partnerSalesChart);
                new window.Chart(barEl, {
                    type: 'bar',
                    data: {
                        labels: salesPayload.labels,
                        datasets: [{
                            label: 'Sales',
                            data: salesPayload.data,
                            backgroundColor: salesPayload.data.map((_, i) => vuexySeries[i % vuexySeries.length]),
                            borderColor: salesPayload.data.map((_, i) => vuexySeriesDark[i % vuexySeriesDark.length]),
                            borderWidth: 1.5,
                            borderRadius: 8,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: { mode: 'index', intersect: false },
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                ticks: { color: '#64748b', font: { size: 11 } }
                            },
                            y: {
                                beginAtZero: true,
                                grid: { color: 'rgba(148,163,184,0.25)' },
                                ticks: { color: '#64748b', font: { size: 11 }, precision: 0 }
                            }
                        }
                    }
                });
            }
        });
    </script>
</x-portal-layout>
