<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Partner\Concerns\ResolvesPartner;
use App\Models\PartnerSale;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SalesController extends Controller
{
    use ResolvesPartner;

    public function index(Request $request): View
    {
        $partner = $this->requirePartner($request);

        $sales = PartnerSale::query()
            ->where('partner_id', $partner->id)
            ->with(['plan', 'membership.members'])
            ->orderByDesc('sold_at')
            ->paginate(20)
            ->withQueryString();

        return view('partner.sales.index', [
            'partner' => $partner,
            'sales' => $sales,
        ]);
    }
}
