<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Partner\Concerns\ResolvesPartner;
use App\Models\Membership;
use App\Models\PartnerSale;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PortalController extends Controller
{
    use ResolvesPartner;

    public function index(Request $request): View
    {
        $partner = $this->requirePartner($request);

        $salesQuery = PartnerSale::query()->where('partner_id', $partner->id);

        $stats = [
            'sales_count' => (clone $salesQuery)->count(),
            'sale_total' => (float) (clone $salesQuery)->sum('sale_amount'),
            'commission_total' => (float) (clone $salesQuery)->sum('commission_amount'),
            'active_memberships' => Membership::query()
                ->where('partner_id', $partner->id)
                ->where('status', 'active')
                ->count(),
        ];

        $recentSales = PartnerSale::query()
            ->where('partner_id', $partner->id)
            ->with(['plan', 'membership.members'])
            ->orderByDesc('sold_at')
            ->limit(8)
            ->get();

        return view('partner.portal', [
            'partner' => $partner,
            'stats' => $stats,
            'recentSales' => $recentSales,
        ]);
    }
}
