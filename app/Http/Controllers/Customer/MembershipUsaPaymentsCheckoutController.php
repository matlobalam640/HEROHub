<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Membership;
use App\Models\Plan;
use App\Services\UsaPaymentsMembershipCheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\View\View;

class MembershipUsaPaymentsCheckoutController extends Controller
{
    public function showRenew(Request $request, UsaPaymentsMembershipCheckoutService $checkoutService): View|RedirectResponse
    {
        abort_unless($checkoutService::isEnabled(), 503, 'USA Payments checkout is not configured.');

        $membership = $this->membershipFor($request);
        abort_unless($membership?->plan, 404);

        $plan = $membership->plan;
        $interval = $this->defaultIntervalFor($plan);

        if (! $checkoutService->canCheckoutPlan($plan, $interval)) {
            return redirect()->route('customer.membership')
                ->with('status', 'Online renewal is not available for your current plan. Please contact support.');
        }

        return $this->renderCheckout($request, $membership, $plan, $interval, 'renew');
    }

    public function showReview(Request $request, string $token, UsaPaymentsMembershipCheckoutService $checkoutService): View|RedirectResponse
    {
        abort_unless($checkoutService::isEnabled(), 503);

        $payload = Cache::get(UsaPaymentsMembershipCheckoutService::REVIEW_CACHE_PREFIX.$token);
        if (! is_array($payload) || (int) ($payload['user_id'] ?? 0) !== (int) $request->user()->id) {
            abort(403);
        }

        $membership = Membership::query()
            ->whereKey($payload['membership_id'])
            ->where('account_user_id', $request->user()->id)
            ->first();
        $plan = Plan::query()->where('active', true)->find($payload['plan_id']);
        $interval = (string) ($payload['interval'] ?? 'yearly');

        if (! $membership || ! $plan || ! $checkoutService->canCheckoutPlan($plan, $interval)) {
            return redirect()->route('customer.membership.plan')
                ->with('status', 'This checkout link has expired or the plan is no longer available.');
        }

        return $this->renderCheckout($request, $membership, $plan, $interval, 'plan_change', $token);
    }

    public function submit(Request $request, UsaPaymentsMembershipCheckoutService $checkoutService): RedirectResponse
    {
        abort_unless($checkoutService::isEnabled(), 503);

        $validated = $request->validate([
            'token' => ['nullable', 'string', 'max:120'],
            'payment_token' => ['required', 'string', 'max:500'],
            'first_name' => ['required', 'string', 'max:80'],
            'last_name' => ['required', 'string', 'max:80'],
            'email' => ['required', 'email', 'max:120'],
            'company' => ['nullable', 'string', 'max:120'],
            'industry' => ['nullable', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:30'],
            'country' => ['required', 'string', 'max:80'],
            'state' => ['required', 'string', 'max:80'],
            'street' => ['required', 'string', 'max:160'],
            'city' => ['required', 'string', 'max:80'],
            'zip_code' => ['required', 'string', 'max:20'],
        ]);

        $user = $request->user();
        $token = trim((string) ($validated['token'] ?? ''));

        if ($token !== '') {
            $payload = Cache::pull(UsaPaymentsMembershipCheckoutService::REVIEW_CACHE_PREFIX.$token);
            if (! is_array($payload) || (int) ($payload['user_id'] ?? 0) !== (int) $user->id) {
                abort(403, 'This checkout link has expired or was already used. Start again from plan selection.');
            }

            $membership = Membership::query()
                ->whereKey($payload['membership_id'])
                ->where('account_user_id', $user->id)
                ->with('members')
                ->firstOrFail();
            $plan = Plan::query()->where('active', true)->findOrFail($payload['plan_id']);
            $interval = (string) $payload['interval'];
        } else {
            $membership = $this->membershipFor($request);
            abort_unless($membership?->plan, 404);
            $membership->load('members');
            $plan = $membership->plan;
            $interval = $this->defaultIntervalFor($plan);
        }

        $checkoutService->processCheckout(
            membership: $membership,
            plan: $plan,
            interval: $interval,
            user: $user,
            paymentToken: $validated['payment_token'],
            billingAddress: $validated,
        );

        $redirectRoute = $token !== '' ? 'customer.membership.plan' : 'customer.membership';

        return redirect()->route($redirectRoute)
            ->with('status', 'Payment successful. Your membership has been renewed and is now active.');
    }

    private function renderCheckout(
        Request $request,
        Membership $membership,
        Plan $plan,
        string $interval,
        string $purpose,
        ?string $token = null,
    ): View {
        $membership->loadMissing('members');
        $primary = $membership->members->firstWhere('is_primary', true) ?? $membership->members->first();
        $checkoutService = app(UsaPaymentsMembershipCheckoutService::class);
        $amounts = $checkoutService->checkoutAmounts($plan, $interval);

        abort_unless($amounts !== null, 422);

        return view('customer.membership.usa-payments-checkout', [
            'membership' => $membership,
            'plan' => $plan,
            'interval' => $interval,
            'intervalLabel' => $interval === 'monthly' ? 'Monthly' : ($plan->billing_interval === 'one_time' ? 'One-time' : 'Annual'),
            'amounts' => $amounts,
            'token' => $token,
            'purpose' => $purpose,
            'primary' => $primary,
            'prefillEmail' => $primary?->email ?: $request->user()->email,
        ]);
    }

    private function membershipFor(Request $request): ?Membership
    {
        return Membership::query()
            ->with(['plan', 'members'])
            ->where('account_user_id', $request->user()->id)
            ->orderByDesc('id')
            ->first();
    }

    private function defaultIntervalFor(Plan $plan): string
    {
        if ($plan->billing_interval === 'one_time') {
            return 'onetime';
        }

        if ($plan->billing_interval === 'monthly') {
            return 'monthly';
        }

        if ((float) ($plan->price_monthly ?? 0) > 0 && (float) ($plan->price ?? 0) <= 0) {
            return 'monthly';
        }

        return 'yearly';
    }

    public static function createReviewToken(Membership $membership, Plan $plan, string $interval, int $userId): string
    {
        $token = Str::random(64);
        Cache::put(
            UsaPaymentsMembershipCheckoutService::REVIEW_CACHE_PREFIX.$token,
            [
                'membership_id' => $membership->id,
                'plan_id' => $plan->id,
                'interval' => $interval,
                'user_id' => $userId,
            ],
            now()->addMinutes(20)
        );

        return $token;
    }
}
