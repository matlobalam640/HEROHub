<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Business\Concerns\ResolvesBusinessCompany;
use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Services\CompanyBillingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BillingController extends Controller
{
    use ResolvesBusinessCompany;

    public function __construct(
        private CompanyBillingService $billingService
    ) {}

    public function edit(Request $request): View|RedirectResponse
    {
        $company = $this->currentCompany($request);
        if (! $company) {
            return redirect()->route('business.portal')
                ->withErrors(['company' => 'No company is available.']);
        }

        $this->billingService->recalculate($company);
        $company->refresh();
        $company->load('defaultPlan');

        $plans = Plan::query()
            ->where('active', true)
            ->whereIn('category', ['business', 'corporate'])
            ->orderBy('name')
            ->get();

        return view('business.billing.edit', [
            'company' => $company,
            'plans' => $plans,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $company = $this->currentCompany($request);
        if (! $company) {
            return redirect()->route('business.portal')->withErrors(['company' => 'No company selected.']);
        }

        $validated = $request->validate([
            'billing_email' => ['nullable', 'email', 'max:255'],
            'default_plan_id' => ['nullable', 'exists:plans,id'],
            'billing_per_employee_override' => ['nullable', 'numeric', 'min:0'],
        ]);

        $company->update([
            'billing_email' => $validated['billing_email'] ?? $company->billing_email,
            'default_plan_id' => ! empty($validated['default_plan_id']) ? (int) $validated['default_plan_id'] : null,
            'billing_per_employee_override' => isset($validated['billing_per_employee_override']) && $validated['billing_per_employee_override'] !== '' && $validated['billing_per_employee_override'] !== null
                ? $validated['billing_per_employee_override']
                : null,
        ]);

        $this->billingService->recalculate($company);

        return redirect()->route('business.billing.edit')->with('status', 'Billing settings saved. Totals recalculated from active employees.');
    }
}
