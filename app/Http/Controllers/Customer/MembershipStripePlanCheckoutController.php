<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Membership;
use App\Models\Plan;
use App\Services\StripeMembershipPlanChangeCheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class MembershipStripePlanCheckoutController extends Controller
{
    public function showReview(Request $request, string $token): View|RedirectResponse
    {
        $user = $request->user();
        $payload = Cache::get(StripeMembershipPlanChangeCheckoutService::REVIEW_CACHE_PREFIX.$token);
        if (! is_array($payload) || (int) ($payload['user_id'] ?? 0) !== (int) $user->id) {
            abort(403);
        }

        $membership = Membership::query()
            ->whereKey($payload['membership_id'])
            ->where('account_user_id', $user->id)
            ->first();
        $plan = Plan::query()->where('active', true)->find($payload['plan_id']);
        if (! $membership || ! $plan) {
            abort(404);
        }

        $interval = (string) $payload['interval'];
        $usd = $plan->unitAmountUsdForStripePlanChange($interval);
        if ($usd === null) {
            return redirect()->route('customer.membership.plan')
                ->with('status', 'This plan is no longer available for Stripe checkout.');
        }

        return view('customer.membership.plan-stripe-review', [
            'token' => $token,
            'membership' => $membership->load('plan'),
            'plan' => $plan,
            'interval' => $interval,
            'usdAmount' => $usd,
        ]);
    }

    public function startCheckout(Request $request, StripeMembershipPlanChangeCheckoutService $checkoutService): RedirectResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string', 'max:120'],
        ]);

        $user = $request->user();
        $token = $validated['token'];
        $payload = Cache::pull(StripeMembershipPlanChangeCheckoutService::REVIEW_CACHE_PREFIX.$token);
        if (! is_array($payload) || (int) ($payload['user_id'] ?? 0) !== (int) $user->id) {
            abort(403, 'This checkout link has expired or was already used. Start again from plan selection.');
        }

        $membership = Membership::query()
            ->whereKey($payload['membership_id'])
            ->where('account_user_id', $user->id)
            ->firstOrFail();
        $plan = Plan::query()->where('active', true)->findOrFail($payload['plan_id']);
        $interval = (string) $payload['interval'];

        if ($plan->id === $membership->plan_id) {
            return redirect()->route('customer.membership.plan')->with('status', 'This is already your active plan.');
        }

        $session = $checkoutService->createCheckoutSession($membership, $plan, $interval, $user);

        return redirect()->away($session->url);
    }

    public function success(Request $request, StripeMembershipPlanChangeCheckoutService $checkoutService): RedirectResponse
    {
        $validated = $request->validate([
            'session_id' => ['required', 'string', 'max:200'],
        ]);

        $ok = $checkoutService->finalizePaidCheckoutSession($validated['session_id'], (int) $request->user()->id);

        if (! $ok) {
            return redirect()->route('customer.membership.plan')
                ->with('status', 'Payment is still processing or could not be verified. If the charge succeeded, your plan will update automatically within a few minutes.');
        }

        return redirect()->route('customer.membership.plan')
            ->with('status', 'Payment successful. Your plan has been updated.');
    }
}
