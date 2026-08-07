<x-portal-layout>
    <div class="hero-dashboard-shell space-y-8">
        {{-- Welcome hero --}}
        <section class="dashboard-welcome overflow-hidden rounded-3xl border border-[#283b69]/20 bg-gradient-to-br from-[#283b69] via-[#324878] to-[#1e2d50] px-6 py-7 text-white shadow-[0_24px_60px_-28px_rgba(40,59,105,0.65)] sm:px-8 sm:py-8">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                <div class="min-w-0">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-white/50">HEROHub admin</p>
                    <h1 class="mt-2 font-display text-2xl font-bold tracking-tight sm:text-3xl">
                        Good {{ now()->format('H') < 12 ? 'morning' : (now()->format('H') < 17 ? 'afternoon' : 'evening') }}, {{ strtok(auth()->user()->name, ' ') }}
                    </h1>
                    <p class="mt-2 max-w-xl text-sm leading-relaxed text-white/70">
                        Live snapshot of memberships, billing, and member activity across the portal.
                        Updated {{ now()->timezone(config('app.timezone'))->format('M j, Y') }}.
                    </p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('portal.coming-soon', ['page' => 'memberships']) }}" class="dashboard-quick-link">
                        <i class="fa-solid fa-id-card-clip" aria-hidden="true"></i>
                        Memberships
                    </a>
                    <a href="{{ route('portal.coming-soon', ['page' => 'customers']) }}" class="dashboard-quick-link">
                        <i class="fa-solid fa-users" aria-hidden="true"></i>
                        Customers
                    </a>
                    <a href="{{ route('dispatch.verification') }}" class="dashboard-quick-link">
                        <i class="fa-solid fa-shield-check" aria-hidden="true"></i>
                        Dispatch
                    </a>
                    <a href="{{ route('portal.coming-soon', ['page' => 'reports']) }}" class="dashboard-quick-link">
                        <i class="fa-solid fa-chart-line" aria-hidden="true"></i>
                        Reports
                    </a>
                </div>
            </div>
        </section>

        {{-- Primary KPIs --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @include('portal.partials.dashboard-stat-card', [
                'href' => route('portal.coming-soon', ['page' => 'memberships']),
                'icon' => 'fa-solid fa-circle-check',
                'iconVariant' => 'vuexy-success',
                'label' => 'Active memberships',
                'value' => number_format($stats['memberships_active']),
                'trend' => $stats['memberships_new_month'] > 0 ? '+'.$stats['memberships_new_month'].' this month' : null,
                'hint' => '<span class="font-semibold text-slate-700">'.number_format($stats['memberships_total']).'</span> total · '
                    .number_format($stats['memberships_expired']).' expired · '
                    .number_format($stats['memberships_cancelled']).' cancelled',
            ])
            @include('portal.partials.dashboard-stat-card', [
                'href' => route('portal.coming-soon', ['page' => 'customers']),
                'icon' => 'fa-solid fa-users',
                'iconVariant' => 'vuexy-primary',
                'label' => 'Member accounts',
                'value' => number_format($stats['customers']),
                'trend' => $stats['customers_new_month'] > 0 ? '+'.$stats['customers_new_month'].' new' : null,
                'hint' => 'Customer portal users with active or past memberships',
            ])
            @include('portal.partials.dashboard-stat-card', [
                'href' => route('portal.coming-soon', ['page' => 'reports']),
                'icon' => 'fa-solid fa-sack-dollar',
                'iconVariant' => 'vuexy-warning',
                'label' => 'Est. monthly revenue',
                'value' => '$'.number_format($stats['estimated_mrr'], 0),
                'hint' => 'Based on active plan pricing · <span class="font-semibold text-slate-700">'.number_format($stats['usa_payments']).'</span> USA Payments synced',
            ])
            @include('portal.partials.dashboard-stat-card', [
                'href' => route('dispatch.verification'),
                'icon' => 'fa-solid fa-clipboard-list',
                'iconVariant' => 'vuexy-info',
                'label' => 'Coverage pending',
                'value' => number_format($stats['coverage_incomplete']),
                'hint' => $stats['renewals_30_days'] > 0
                    ? '<span class="font-semibold text-slate-700">'.number_format($stats['renewals_30_days']).'</span> renewals in the next 30 days'
                    : 'Active memberships missing required coverage details',
            ])
        </div>

        {{-- Charts --}}
        <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
            <div class="hero-dashboard-panel overflow-hidden rounded-2xl border border-slate-100 bg-white xl:col-span-2">
                <div class="dashboard-card-header flex items-center justify-between gap-3">
                    <div>
                        <div class="dashboard-card-header__title">Membership growth</div>
                        <div class="dashboard-card-header__sub">New memberships per month · last 12 months</div>
                    </div>
                    <span class="hidden rounded-full bg-[rgba(115,103,240,0.1)] px-3 py-1 text-xs font-semibold text-[color:var(--vuexy-primary)] sm:inline">
                        {{ number_format($stats['memberships_new_month']) }} this month
                    </span>
                </div>
                <div class="p-6">
                    <div class="h-72">
                        <canvas id="membershipGrowthChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="hero-dashboard-panel overflow-hidden rounded-2xl border border-slate-100 bg-white">
                <div class="dashboard-card-header">
                    <div class="dashboard-card-header__title">Status breakdown</div>
                    <div class="dashboard-card-header__sub">All memberships by status</div>
                </div>
                <div class="p-6">
                    @if(count($membershipStatusChart['labels']))
                        <div class="mx-auto h-44 w-44 max-w-full">
                            <canvas id="membershipStatusChart"></canvas>
                        </div>
                        <div class="mt-4 grid grid-cols-2 gap-2">
                            @foreach($membershipStatusChart['labels'] as $i => $label)
                                <div class="rounded-xl border border-slate-100 bg-slate-50/80 px-3 py-2">
                                    <div class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">{{ $label }}</div>
                                    <div class="mt-0.5 text-lg font-bold tabular-nums text-slate-900">{{ number_format($membershipStatusChart['data'][$i]) }}</div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="py-12 text-center text-sm text-slate-500">No memberships yet.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
            <div class="hero-dashboard-panel overflow-hidden rounded-2xl border border-slate-100 bg-white">
                <div class="dashboard-card-header">
                    <div class="dashboard-card-header__title">Active plan mix</div>
                    <div class="dashboard-card-header__sub">Distribution across live memberships</div>
                </div>
                <div class="p-6">
                    @if(count($planMixChart['labels']))
                        <div class="h-64">
                            <canvas id="planMixChart"></canvas>
                        </div>
                    @else
                        <p class="py-12 text-center text-sm text-slate-500">No active memberships to chart yet.</p>
                    @endif
                </div>
            </div>

            <div class="hero-dashboard-panel overflow-hidden rounded-2xl border border-slate-100 bg-white">
                <div class="dashboard-card-header flex items-center justify-between gap-3">
                    <div>
                        <div class="dashboard-card-header__title">
                            {{ $stats['partner_sales'] > 0 ? 'Partner sales' : 'Upcoming renewals' }}
                        </div>
                        <div class="dashboard-card-header__sub">
                            {{ $stats['partner_sales'] > 0 ? 'Sales recorded per month · last 6 months' : 'Next billing dates on file' }}
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    @if($stats['partner_sales'] > 0)
                        <div class="h-64">
                            <canvas id="partnerSalesChart"></canvas>
                        </div>
                    @elseif($upcomingRenewals->isNotEmpty())
                        <ul class="dashboard-renewal-list space-y-3">
                            @foreach($upcomingRenewals as $membership)
                                @php
                                    $primary = $membership->primaryMember;
                                    $memberLabel = $primary
                                        ? trim($primary->first_name.' '.$primary->last_name)
                                        : $membership->membership_number;
                                @endphp
                                <li class="dashboard-renewal-item flex items-center justify-between gap-3 rounded-xl border border-slate-100 bg-slate-50/70 px-4 py-3">
                                    <div class="min-w-0">
                                        <div class="truncate font-mono text-xs font-semibold text-slate-800">{{ $membership->membership_number }}</div>
                                        <div class="truncate text-sm text-slate-600">{{ $memberLabel }}</div>
                                        <div class="truncate text-xs text-slate-500">{{ $membership->plan?->name ?? '—' }}</div>
                                    </div>
                                    <div class="shrink-0 text-right">
                                        <div class="text-sm font-bold tabular-nums text-slate-900">
                                            {{ $membership->billing_next_billing_at?->format('M j') }}
                                        </div>
                                        <div class="text-[11px] text-slate-500">
                                            {{ $membership->billing_next_billing_at?->diffForHumans() }}
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="flex flex-col items-center justify-center py-12 text-center">
                            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                                <i class="fa-regular fa-calendar-check text-xl" aria-hidden="true"></i>
                            </div>
                            <p class="mt-4 text-sm font-medium text-slate-700">No upcoming renewals scheduled</p>
                            <p class="mt-1 max-w-xs text-xs text-slate-500">Billing dates appear here once synced from USA Payments.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Tables + activity --}}
        <div class="grid grid-cols-1 gap-6 xl:grid-cols-5">
            <div class="hero-dashboard-panel overflow-hidden rounded-2xl border border-slate-100 bg-white xl:col-span-3">
                <div class="dashboard-card-header flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <div class="dashboard-card-header__title">Recent memberships</div>
                        <div class="dashboard-card-header__sub">Latest records from production data</div>
                    </div>
                    <a href="{{ route('portal.coming-soon', ['page' => 'memberships']) }}" class="text-xs font-semibold text-[color:var(--vuexy-primary)] hover:underline">
                        View all
                    </a>
                </div>
                <div class="overflow-x-auto p-2">
                    <div class="hero-datatable min-w-[640px]">
                        <table class="js-datatable w-full text-sm" data-dt-per-page="8">
                            <thead class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                <tr>
                                    <th class="px-3 py-2">Number</th>
                                    <th class="px-3 py-2">Member</th>
                                    <th class="px-3 py-2">Plan</th>
                                    <th class="px-3 py-2">Billing</th>
                                    <th class="px-3 py-2">Status</th>
                                    <th class="px-3 py-2"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentMemberships as $membership)
                                    @php
                                        $primary = $membership->primaryMember;
                                        $memberName = $primary
                                            ? trim($primary->first_name.' '.$primary->last_name)
                                            : ($membership->accountUser?->name ?? '—');
                                    @endphp
                                    <tr>
                                        <td class="px-3 py-2.5 font-mono text-xs font-semibold text-slate-800">{{ $membership->membership_number }}</td>
                                        <td class="px-3 py-2.5 text-slate-700">{{ $memberName }}</td>
                                        <td class="px-3 py-2.5 text-slate-600">{{ $membership->plan?->name ?? '—' }}</td>
                                        <td class="px-3 py-2.5 text-xs text-slate-500">
                                            @if($membership->billing_provider)
                                                <span class="font-medium text-slate-700">{{ strtoupper(str_replace('_', ' ', $membership->billing_provider)) }}</span>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td class="px-3 py-2.5">
                                            @include('portal.partials.membership-status-badge', ['status' => $membership->status])
                                        </td>
                                        <td class="px-3 py-2.5 text-right">
                                            @if(auth()->user()->hasAnyRole(['admin', 'dispatch']))
                                                <a href="{{ route('portal.membership.show', $membership) }}" class="text-xs font-semibold text-[color:var(--vuexy-primary)] hover:underline">Open</a>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-3 py-8 text-center text-slate-500">No memberships yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="hero-dashboard-panel overflow-hidden rounded-2xl border border-slate-100 bg-white xl:col-span-2">
                <div class="dashboard-card-header">
                    <div class="dashboard-card-header__title">Recent activity</div>
                    <div class="dashboard-card-header__sub">Memberships and partner sales</div>
                </div>
                <div class="p-4">
                    @forelse($recentActivity as $row)
                        <div class="dashboard-activity-item flex gap-3 py-3 first:pt-0 last:pb-0">
                            <div @class([
                                'dashboard-activity-icon',
                                'dashboard-activity-icon--membership' => $row['kind'] === 'membership',
                                'dashboard-activity-icon--sale' => $row['kind'] === 'sale',
                            ])>
                                <i class="{{ $row['kind'] === 'membership' ? 'fa-solid fa-id-card' : 'fa-solid fa-handshake' }}" aria-hidden="true"></i>
                            </div>
                            <div class="min-w-0 flex-1 border-b border-slate-100 pb-3 last:border-0">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="min-w-0">
                                        <div class="truncate font-semibold text-slate-800">{{ $row['title'] }}</div>
                                        <div class="truncate text-sm text-slate-600">{{ $row['detail'] }}</div>
                                        @if(! empty($row['meta']))
                                            <div class="truncate text-xs text-slate-500">{{ $row['meta'] }}</div>
                                        @endif
                                    </div>
                                    <time class="shrink-0 whitespace-nowrap text-[11px] text-slate-400">
                                        {{ $row['at']->timezone(config('app.timezone'))->diffForHumans() }}
                                    </time>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="py-10 text-center text-sm text-slate-500">No activity yet.</p>
                    @endforelse
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
            const V = {
                primary: vx('--vuexy-primary', '#7367f0'),
                success: vx('--vuexy-success', '#28c76f'),
                warning: vx('--vuexy-warning', '#ff9f43'),
                danger: vx('--vuexy-danger', '#ea5455'),
                info: vx('--vuexy-info', '#00cfe8'),
                secondary: vx('--vuexy-secondary', '#82868b'),
            };
            const vuexySeries = [V.primary, V.success, V.warning, V.danger, V.info, V.secondary, '#a8aaae', '#283b69'];
            const gridColor = 'rgba(148,163,184,0.25)';
            const tickColor = '#64748b';

            const lineEl = document.getElementById('membershipGrowthChart');
            if (lineEl) {
                const payload = @json($membershipChart);
                const ctx = lineEl.getContext('2d');
                const h = lineEl.offsetHeight || 288;
                const lineFill = ctx.createLinearGradient(0, 0, 0, h);
                lineFill.addColorStop(0, 'rgba(115, 103, 240, 0.35)');
                lineFill.addColorStop(0.55, 'rgba(115, 103, 240, 0.12)');
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
                            tension: 0.4,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            pointBackgroundColor: '#fff',
                            pointBorderColor: V.primary,
                            pointBorderWidth: 2,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            x: { grid: { display: false }, ticks: { color: tickColor, font: { size: 11 } } },
                            y: { beginAtZero: true, grid: { color: gridColor }, ticks: { color: tickColor, font: { size: 11 }, precision: 0 } }
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
                        cutout: '68%',
                        plugins: { legend: { display: false } }
                    }
                });
            }

            const planMixEl = document.getElementById('planMixChart');
            if (planMixEl) {
                const planPayload = @json($planMixChart);
                new window.Chart(planMixEl, {
                    type: 'bar',
                    data: {
                        labels: planPayload.labels,
                        datasets: [{
                            label: 'Active memberships',
                            data: planPayload.data,
                            backgroundColor: planPayload.data.map((_, i) => vuexySeries[i % vuexySeries.length]),
                            borderRadius: 8,
                            borderSkipped: false,
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            x: { beginAtZero: true, grid: { color: gridColor }, ticks: { color: tickColor, precision: 0 } },
                            y: { grid: { display: false }, ticks: { color: tickColor, font: { size: 11 } } }
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
                            backgroundColor: V.warning,
                            borderRadius: 8,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            x: { grid: { display: false }, ticks: { color: tickColor } },
                            y: { beginAtZero: true, grid: { color: gridColor }, ticks: { color: tickColor, precision: 0 } }
                        }
                    }
                });
            }
        });
    </script>
</x-portal-layout>
