<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Membership;
use App\Models\Partner;
use App\Models\PartnerSale;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use App\Support\CoverageProfileRequirement;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        if (RouteServiceProvider::isCustomerPortalOnly($request->user())) {
            return redirect()->route('customer.membership');
        }

        if (RouteServiceProvider::isBusinessPortalPrimary($request->user())) {
            return redirect()->route('business.portal');
        }

        $monthStart = now()->startOfMonth();
        $statusCounts = Membership::query()
            ->selectRaw('status, COUNT(*) as c')
            ->groupBy('status')
            ->pluck('c', 'status')
            ->all();

        $stats = [
            'customers' => User::role('customer')->count(),
            'customers_new_month' => User::role('customer')->where('created_at', '>=', $monthStart)->count(),
            'memberships_total' => Membership::count(),
            'memberships_active' => (int) ($statusCounts['active'] ?? 0),
            'memberships_inactive' => (int) ($statusCounts['inactive'] ?? 0),
            'memberships_expired' => (int) ($statusCounts['expired'] ?? 0),
            'memberships_cancelled' => (int) ($statusCounts['cancelled'] ?? 0),
            'memberships_new_month' => Membership::query()
                ->whereRaw($this->membershipStartDateExpression().' >= ?', [$monthStart->toDateString()])
                ->count(),
            'usa_payments' => Membership::where('billing_provider', 'usa_payments')->count(),
            'companies' => Company::count(),
            'partners' => Partner::count(),
            'partners_active' => Partner::where('active', true)->count(),
            'partner_sales' => PartnerSale::count(),
            'estimated_mrr' => $this->estimateMonthlyRecurringRevenue(),
            'coverage_incomplete' => $this->countIncompleteCoverage(),
            'renewals_30_days' => Membership::query()
                ->where('status', 'active')
                ->whereNotNull('billing_next_billing_at')
                ->whereBetween('billing_next_billing_at', [now()->startOfDay(), now()->addDays(30)])
                ->count(),
        ];

        $recentMemberships = Membership::query()
            ->with(['plan', 'company', 'partner', 'primaryMember', 'accountUser'])
            ->latest('id')
            ->limit(10)
            ->get();

        $membershipChart = $this->membershipsPerMonthChart(12);
        $membershipStatusChart = $this->membershipStatusChart($statusCounts);
        $planMixChart = $this->planMixChart();
        $partnerSalesChart = $this->partnerSalesPerMonthChart(6);

        $upcomingRenewals = Membership::query()
            ->where('status', 'active')
            ->whereNotNull('billing_next_billing_at')
            ->where('billing_next_billing_at', '>=', now()->startOfDay())
            ->with(['plan', 'primaryMember'])
            ->orderBy('billing_next_billing_at')
            ->limit(6)
            ->get();

        $recentActivity = $this->recentActivityFeed();

        return view('dashboard', [
            'stats' => $stats,
            'recentMemberships' => $recentMemberships,
            'membershipChart' => $membershipChart,
            'membershipStatusChart' => $membershipStatusChart,
            'planMixChart' => $planMixChart,
            'partnerSalesChart' => $partnerSalesChart,
            'upcomingRenewals' => $upcomingRenewals,
            'recentActivity' => $recentActivity,
        ]);
    }

    private function estimateMonthlyRecurringRevenue(): float
    {
        return round((float) Membership::query()
            ->where('status', 'active')
            ->with('plan')
            ->get()
            ->sum(function (Membership $membership) {
                $plan = $membership->plan;
                if (! $plan) {
                    return 0;
                }

                if ($plan->billing_interval === 'yearly') {
                    $yearly = (float) ($plan->price ?? 0);
                    if ($yearly <= 0) {
                        $yearly = (float) ($plan->price_monthly ?? 0) * 12;
                    }

                    return $yearly > 0 ? $yearly / 12 : 0;
                }

                $monthly = (float) ($plan->price_monthly ?? 0);
                if ($monthly <= 0) {
                    $monthly = (float) ($plan->price ?? 0);
                }

                return $monthly > 0 ? $monthly : 0;
            }), 2);
    }

    private function countIncompleteCoverage(): int
    {
        return Membership::query()
            ->where('status', 'active')
            ->with(['plan', 'members', 'dependents', 'coverageProfile'])
            ->get()
            ->filter(fn (Membership $membership) => ! CoverageProfileRequirement::isComplete($membership))
            ->count();
    }

    /**
     * @return array{labels: list<string>, data: list<int>}
     */
    private function membershipsPerMonthChart(int $months): array
    {
        $labels = [];
        $buckets = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $labels[] = now()->subMonths($i)->format('M Y');
            $buckets[now()->subMonths($i)->format('Y-m')] = 0;
        }

        $startExpr = $this->membershipStartDateExpression();
        $groupExpr = $this->monthGroupExpression($startExpr);

        $rows = Membership::query()
            ->selectRaw("$groupExpr as ym, COUNT(*) as c")
            ->whereRaw("$startExpr >= ?", [now()->subMonths($months - 1)->startOfMonth()->toDateString()])
            ->groupByRaw($groupExpr)
            ->pluck('c', 'ym')
            ->all();

        foreach ($rows as $ym => $count) {
            if (array_key_exists($ym, $buckets)) {
                $buckets[$ym] = (int) $count;
            }
        }

        return [
            'labels' => $labels,
            'data' => array_values($buckets),
        ];
    }

    /**
     * @param  array<string, int|string>  $statusCounts
     * @return array{labels: list<string>, data: list<int>}
     */
    private function membershipStatusChart(array $statusCounts): array
    {
        $statusOrder = ['active', 'inactive', 'expired', 'cancelled'];
        $labels = [];
        $data = [];

        foreach ($statusOrder as $status) {
            if (! empty($statusCounts[$status])) {
                $labels[] = ucfirst($status);
                $data[] = (int) $statusCounts[$status];
            }
        }

        foreach ($statusCounts as $status => $count) {
            if (! in_array($status, $statusOrder, true) && $count > 0) {
                $labels[] = ucfirst((string) $status);
                $data[] = (int) $count;
            }
        }

        return ['labels' => $labels, 'data' => $data];
    }

    /**
     * @return array{labels: list<string>, data: list<int>}
     */
    private function planMixChart(): array
    {
        $rows = Membership::query()
            ->where('status', 'active')
            ->join('plans', 'memberships.plan_id', '=', 'plans.id')
            ->selectRaw('plans.name as plan_name, COUNT(*) as c')
            ->groupBy('plans.id', 'plans.name')
            ->orderByDesc('c')
            ->limit(8)
            ->get();

        return [
            'labels' => $rows->pluck('plan_name')->all(),
            'data' => $rows->pluck('c')->map(fn ($c) => (int) $c)->all(),
        ];
    }

    /**
     * @return array{labels: list<string>, data: list<int>}
     */
    private function partnerSalesPerMonthChart(int $months): array
    {
        $labels = [];
        $buckets = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $labels[] = now()->subMonths($i)->format('M Y');
            $buckets[now()->subMonths($i)->format('Y-m')] = 0;
        }

        $groupExpr = $this->monthGroupExpression('sold_at');

        $rows = PartnerSale::query()
            ->selectRaw("$groupExpr as ym, COUNT(*) as c")
            ->where('sold_at', '>=', now()->subMonths($months - 1)->startOfMonth())
            ->groupBy('ym')
            ->pluck('c', 'ym')
            ->all();

        foreach ($rows as $ym => $count) {
            if (array_key_exists($ym, $buckets)) {
                $buckets[$ym] = (int) $count;
            }
        }

        return [
            'labels' => $labels,
            'data' => array_values($buckets),
        ];
    }

    private function monthGroupExpression(string $column): string
    {
        return DB::getDriverName() === 'sqlite'
            ? "strftime('%Y-%m', {$column})"
            : "DATE_FORMAT({$column}, '%Y-%m')";
    }

    private function membershipStartDateExpression(): string
    {
        if (DB::getDriverName() === 'sqlite') {
            return "date(COALESCE(coverage_starts_on, billing_subscription_created_at, created_at))";
        }

        return 'COALESCE(coverage_starts_on, DATE(billing_subscription_created_at), DATE(created_at))';
    }

    private function membershipActivityTimestamp(Membership $membership): \Illuminate\Support\Carbon
    {
        if ($membership->coverage_starts_on !== null) {
            return $membership->coverage_starts_on->copy()->startOfDay();
        }

        if ($membership->billing_subscription_created_at !== null) {
            return $membership->billing_subscription_created_at->copy()->startOfDay();
        }

        return $membership->created_at->copy();
    }

    /**
     * @return Collection<int, array{kind: string, title: string, detail: string, meta: ?string, at: \Illuminate\Support\Carbon|\DateTimeInterface}>
     */
    private function recentActivityFeed(): Collection
    {
        $membershipEvents = Membership::query()
            ->with(['plan', 'primaryMember'])
            ->latest('created_at')
            ->limit(10)
            ->get()
            ->map(function (Membership $membership) {
                $primary = $membership->primaryMember;
                $memberName = $primary
                    ? trim($primary->first_name.' '.$primary->last_name)
                    : null;

                return [
                    'kind' => 'membership',
                    'title' => $membership->membership_number,
                    'detail' => $membership->plan?->name ?? 'Membership created',
                    'meta' => $memberName ?: null,
                    'at' => $this->membershipActivityTimestamp($membership),
                ];
            });

        $saleEvents = PartnerSale::query()
            ->with('partner')
            ->latest('sold_at')
            ->limit(6)
            ->get()
            ->map(function (PartnerSale $sale) {
                $amount = $sale->sale_amount !== null
                    ? '$'.number_format((float) $sale->sale_amount, 2)
                    : null;

                return [
                    'kind' => 'sale',
                    'title' => $sale->partner?->name ?? 'Partner sale',
                    'detail' => $amount ? "Sale {$amount}" : 'Partner sale recorded',
                    'meta' => null,
                    'at' => $sale->sold_at ?? $sale->created_at,
                ];
            });

        return $membershipEvents
            ->concat($saleEvents)
            ->sortByDesc(fn (array $row) => $row['at']->getTimestamp())
            ->take(12)
            ->values();
    }
}
