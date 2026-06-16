<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Membership;
use App\Models\Partner;
use App\Models\PartnerSale;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
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

        $stats = [
            'customers' => User::role('customer')->count(),
            'memberships_total' => Membership::count(),
            'memberships_active' => Membership::where('status', 'active')->count(),
            'companies' => Company::count(),
            'partners' => Partner::count(),
            'partner_sales' => PartnerSale::count(),
        ];

        $recentMemberships = Membership::query()
            ->with(['plan', 'company', 'members'])
            ->latest('id')
            ->limit(8)
            ->get();

        // Chart: memberships created per month (last 12 months)
        $labels = [];
        $buckets = [];
        for ($i = 11; $i >= 0; $i--) {
            $labels[] = now()->subMonths($i)->format('M Y');
            $buckets[now()->subMonths($i)->format('Y-m')] = 0;
        }

        $driver = DB::getDriverName();
        $groupExpr = $driver === 'sqlite'
            ? "strftime('%Y-%m', created_at)"
            : "DATE_FORMAT(created_at, '%Y-%m')";

        $rows = Membership::query()
            ->selectRaw("$groupExpr as ym, COUNT(*) as c")
            ->where('created_at', '>=', now()->subMonths(11)->startOfMonth())
            ->groupBy('ym')
            ->pluck('c', 'ym')
            ->all();

        foreach ($rows as $ym => $count) {
            if (array_key_exists($ym, $buckets)) {
                $buckets[$ym] = (int) $count;
            }
        }

        $membershipChart = [
            'labels' => $labels,
            'data' => array_values($buckets),
        ];

        // Doughnut: membership status distribution
        $statusCounts = Membership::query()
            ->selectRaw('status, COUNT(*) as c')
            ->groupBy('status')
            ->pluck('c', 'status')
            ->all();

        $statusOrder = ['active', 'inactive', 'expired', 'cancelled'];
        $membershipStatusChart = [
            'labels' => [],
            'data' => [],
        ];
        foreach ($statusOrder as $st) {
            if (! empty($statusCounts[$st])) {
                $membershipStatusChart['labels'][] = ucfirst($st);
                $membershipStatusChart['data'][] = (int) $statusCounts[$st];
            }
        }
        foreach ($statusCounts as $st => $c) {
            if (! in_array($st, $statusOrder, true) && $c > 0) {
                $membershipStatusChart['labels'][] = ucfirst((string) $st);
                $membershipStatusChart['data'][] = (int) $c;
            }
        }

        // Bar chart: partner sales count per month (last 6 months)
        $saleLabels = [];
        $saleBuckets = [];
        for ($i = 5; $i >= 0; $i--) {
            $saleLabels[] = now()->subMonths($i)->format('M Y');
            $saleBuckets[now()->subMonths($i)->format('Y-m')] = 0;
        }

        $saleGroupExpr = $driver === 'sqlite'
            ? "strftime('%Y-%m', sold_at)"
            : "DATE_FORMAT(sold_at, '%Y-%m')";

        $saleRows = PartnerSale::query()
            ->selectRaw("$saleGroupExpr as ym, COUNT(*) as c")
            ->where('sold_at', '>=', now()->subMonths(5)->startOfMonth())
            ->groupBy('ym')
            ->pluck('c', 'ym')
            ->all();

        foreach ($saleRows as $ym => $count) {
            if (array_key_exists($ym, $saleBuckets)) {
                $saleBuckets[$ym] = (int) $count;
            }
        }

        $partnerSalesChart = [
            'labels' => $saleLabels,
            'data' => array_values($saleBuckets),
        ];

        // Recent activity (memberships created + partner sales)
        $membershipEvents = Membership::query()
            ->with('plan')
            ->latest('created_at')
            ->limit(8)
            ->get()
            ->map(function (Membership $m) {
                return [
                    'kind' => 'membership',
                    'title' => 'Membership '.$m->membership_number,
                    'detail' => $m->plan?->name ?? '—',
                    'at' => $m->created_at,
                ];
            });

        $saleEvents = PartnerSale::query()
            ->with('partner')
            ->latest('sold_at')
            ->limit(8)
            ->get()
            ->map(function (PartnerSale $s) {
                return [
                    'kind' => 'sale',
                    'title' => 'Partner sale',
                    'detail' => $s->partner?->name ?? 'Partner #'.$s->partner_id,
                    'at' => $s->sold_at ?? $s->created_at,
                ];
            });

        $recentActivity = $membershipEvents
            ->concat($saleEvents)
            ->sortByDesc(fn (array $row) => $row['at']->timestamp)
            ->take(12)
            ->values();

        return view('dashboard', [
            'stats' => $stats,
            'recentMemberships' => $recentMemberships,
            'membershipChart' => $membershipChart,
            'membershipStatusChart' => $membershipStatusChart,
            'partnerSalesChart' => $partnerSalesChart,
            'recentActivity' => $recentActivity,
        ]);
    }
}
