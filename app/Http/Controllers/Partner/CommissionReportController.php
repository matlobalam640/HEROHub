<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Partner\Concerns\ResolvesPartner;
use App\Models\PartnerSale;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommissionReportController extends Controller
{
    use ResolvesPartner;

    public function __invoke(Request $request): View
    {
        $partner = $this->requirePartner($request);

        $totals = (object) [
            'sale_count' => PartnerSale::query()->where('partner_id', $partner->id)->count(),
            'sale_sum' => (float) PartnerSale::query()->where('partner_id', $partner->id)->sum('sale_amount'),
            'commission_sum' => (float) PartnerSale::query()->where('partner_id', $partner->id)->sum('commission_amount'),
        ];

        $byMonth = PartnerSale::query()
            ->where('partner_id', $partner->id)
            ->where('sold_at', '>=', now()->subMonths(36))
            ->get(['sale_amount', 'commission_amount', 'sold_at'])
            ->groupBy(fn ($sale) => $sale->sold_at->format('Y-m'))
            ->map(fn ($group) => (object) [
                'ym' => $group->first()->sold_at->format('Y-m'),
                'sale_count' => $group->count(),
                'sale_sum' => (float) $group->sum('sale_amount'),
                'commission_sum' => (float) $group->sum('commission_amount'),
            ])
            ->values()
            ->sortByDesc('ym')
            ->values();

        return view('partner.commissions', [
            'partner' => $partner,
            'totals' => $totals,
            'byMonth' => $byMonth,
        ]);
    }
}
