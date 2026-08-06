<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Membership;
use App\Services\UsaPaymentsPaymentMethodService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MembershipPaymentMethodController extends Controller
{
    public function show(Request $request): View
    {
        $membership = $this->membershipFor($request);
        abort_unless($membership, 404);

        return view('customer.membership.payment-method', [
            'membership' => $membership,
            'usaPaymentsEnabled' => UsaPaymentsPaymentMethodService::isEnabled(),
            'canUpdateCardOnline' => UsaPaymentsPaymentMethodService::isEnabled()
                && $membership->billing_provider === 'usa_payments'
                && filled($membership->billing_subscription_id),
        ]);
    }

    public function updateBillingMeta(Request $request): RedirectResponse
    {
        $membership = $this->membershipFor($request);
        abort_unless($membership, 404);

        $validated = $request->validate([
            'billing_provider' => ['nullable', 'in:stripe,manual,usa_payments'],
            'billing_customer_id' => ['nullable', 'string', 'max:120'],
        ]);

        $membership->update($validated);

        return back()->with('status', 'Billing details saved.');
    }

    public function updateUsaPaymentsCard(Request $request, UsaPaymentsPaymentMethodService $paymentMethodService): RedirectResponse
    {
        abort_unless(UsaPaymentsPaymentMethodService::isEnabled(), 503);

        $membership = $this->membershipFor($request);
        abort_unless($membership, 404);

        $validated = $request->validate([
            'payment_token' => ['required', 'string', 'max:500'],
        ]);

        $paymentMethodService->updatePaymentMethod(
            membership: $membership,
            user: $request->user(),
            paymentToken: $validated['payment_token'],
        );

        return redirect()->route('customer.membership.billing')
            ->with('status', 'Your payment method was updated successfully.');
    }

    private function membershipFor(Request $request): ?Membership
    {
        return Membership::query()
            ->with(['plan'])
            ->where('account_user_id', $request->user()->id)
            ->orderByDesc('id')
            ->first();
    }
}
