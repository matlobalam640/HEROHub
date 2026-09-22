<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Business\Concerns\ResolvesBusinessCompany;
use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Services\BusinessPlanCheckoutService;
use App\Services\UsaPaymentsMembershipCheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlanCheckoutController extends Controller
{
    use ResolvesBusinessCompany;

    public function __construct(
        private readonly BusinessPlanCheckoutService $checkoutService,
    ) {}

    public function show(Request $request): View|RedirectResponse
    {
        $company = $this->currentCompany($request);
        if (! $company) {
            return redirect()->route('business.portal')
                ->withErrors(['company' => 'No company is linked to your account.']);
        }

        if ($company->isEnrollmentActive()) {
            return redirect()->route('business.portal')
                ->with('status', 'Your company plan is already active.');
        }

        abort_unless(UsaPaymentsMembershipCheckoutService::isEnabled(), 503, 'USA Payments is not configured.');

        $plans = Plan::query()
            ->where('active', true)
            ->whereIn('category', BusinessPlanCheckoutService::ALLOWED_CATEGORIES)
            ->orderBy('category')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->filter(function (Plan $plan) {
                return $this->checkoutService->canCheckoutPlan($plan, 'yearly')
                    || $this->checkoutService->canCheckoutPlan($plan, 'monthly');
            })
            ->values();

        $owner = $request->user();

        return view('business.plan-checkout', [
            'company' => $company,
            'plans' => $plans,
            'owner' => $owner,
            'tokenizationKey' => config('usa_payments.tokenization_key'),
            'collectJsUrl' => config('usa_payments.collect_js_url'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $company = $this->currentCompany($request);
        if (! $company) {
            return redirect()->route('business.portal')
                ->withErrors(['company' => 'No company is linked to your account.']);
        }

        abort_unless(UsaPaymentsMembershipCheckoutService::isEnabled(), 503);

        $validated = $request->validate([
            'plan_id' => ['required', 'integer', 'exists:plans,id'],
            'interval' => ['required', 'in:monthly,yearly'],
            'payment_token' => ['required', 'string', 'max:500'],
            'first_name' => ['required', 'string', 'max:80'],
            'last_name' => ['required', 'string', 'max:80'],
            'email' => ['required', 'email', 'max:120'],
            'phone' => ['required', 'string', 'max:30'],
            'country' => ['required', 'string', 'max:80'],
            'state' => ['required', 'string', 'max:80'],
            'street' => ['required', 'string', 'max:160'],
            'city' => ['required', 'string', 'max:80'],
            'zip_code' => ['required', 'string', 'max:20'],
        ]);

        $plan = Plan::query()->whereKey($validated['plan_id'])->where('active', true)->firstOrFail();
        if (! $this->checkoutService->canCheckoutPlan($plan, $validated['interval'])) {
            return back()
                ->withInput()
                ->withErrors(['plan_id' => 'This plan is not available for USA Payments checkout.']);
        }

        $this->checkoutService->processCheckout(
            company: $company,
            plan: $plan,
            interval: $validated['interval'],
            owner: $request->user(),
            paymentToken: $validated['payment_token'],
            billingAddress: $validated,
        );

        return redirect()
            ->route('business.employees.index')
            ->with('status', 'Payment successful. Your company is active — download the employee CSV template and invite your team.');
    }

    private function defaultInterval(Plan $plan): string
    {
        if ($plan->billing_interval === 'monthly') {
            return 'monthly';
        }

        return 'yearly';
    }
}
