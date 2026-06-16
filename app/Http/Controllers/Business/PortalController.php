<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Business\Concerns\ResolvesBusinessCompany;
use App\Http\Controllers\Controller;
use App\Models\Membership;
use App\Services\CompanyBillingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PortalController extends Controller
{
    use ResolvesBusinessCompany;

    public function __construct(
        private CompanyBillingService $billingService
    ) {}

    public function index(Request $request): View
    {
        $companies = $this->ownedCompanies($request);
        $company = $this->currentCompany($request);

        if ($company) {
            $this->billingService->recalculate($company);
            $company->refresh();
            $company->load('defaultPlan');
        }

        $stats = [
            'active' => 0,
            'inactive' => 0,
            'other' => 0,
        ];

        if ($company) {
            $stats['active'] = Membership::query()->where('company_id', $company->id)->where('status', 'active')->count();
            $stats['inactive'] = Membership::query()->where('company_id', $company->id)->where('status', 'inactive')->count();
            $stats['other'] = Membership::query()->where('company_id', $company->id)->whereNotIn('status', ['active', 'inactive'])->count();
        }

        return view('business.portal', [
            'companies' => $companies,
            'company' => $company,
            'stats' => $stats,
        ]);
    }

    public function switchCompany(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'company_id' => ['required', 'integer'],
        ]);

        $this->findOwnedCompany($request, (int) $validated['company_id']);
        $request->session()->put('business_company_id', (int) $validated['company_id']);

        return redirect()->route('business.portal')->with('status', 'Company switched.');
    }
}
