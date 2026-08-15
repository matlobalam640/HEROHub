<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Membership;
use App\Models\Plan;
use App\Services\UsaPaymentsMembershipCheckoutService;
use App\Services\WalkInEnrollmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\View\View;

class WalkInEnrollmentController extends Controller
{
    public const CHECKOUT_CACHE_PREFIX = 'admin_walk_in_checkout:';

    public function __construct(
        private readonly WalkInEnrollmentService $enrollmentService,
        private readonly UsaPaymentsMembershipCheckoutService $checkoutService,
    ) {}

    public function index(Request $request): View
    {
        abort_unless($request->user()?->hasAnyRole(['admin', 'dispatch']), 403);

        $plans = Plan::query()
            ->where('category', 'retail')
            ->where('active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('admin.enrollment.index', [
            'plans' => $plans,
            'usaPaymentsEnabled' => UsaPaymentsMembershipCheckoutService::isEnabled(),
            'enrollmentResult' => $request->session()->get('walk_in_enrollment_result'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->hasAnyRole(['admin', 'dispatch']), 403);

        $validated = $request->validate([
            'plan_id' => ['required', 'integer', 'exists:plans,id'],
            'interval' => ['nullable', 'in:onetime,monthly,yearly'],
            'payment_method' => ['required', 'in:usa_payments,manual'],
            'first_name' => ['required', 'string', 'max:80'],
            'last_name' => ['required', 'string', 'max:80'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
        ]);

        $plan = Plan::query()
            ->whereKey($validated['plan_id'])
            ->where('category', 'retail')
            ->where('active', true)
            ->firstOrFail();

        $interval = $this->resolveInterval($plan, (string) ($validated['interval'] ?? ''));

        if ($validated['payment_method'] === 'usa_payments') {
            abort_unless(UsaPaymentsMembershipCheckoutService::isEnabled(), 422, 'USA Payments is not configured.');

            if (! $this->checkoutService->canCheckoutPlan($plan, $interval)) {
                return back()
                    ->withInput()
                    ->withErrors(['plan_id' => 'This plan is not available for USA Payments checkout. Choose manual payment or another plan.']);
            }

            $created = $this->enrollmentService->createPendingRetailEnrollment($validated);

            $token = $this->createCheckoutToken(
                membership: $created['membership'],
                plan: $created['plan'],
                interval: $interval,
                adminUserId: (int) $request->user()->id,
            );

            return redirect()
                ->route('admin.enrollment.checkout', ['token' => $token])
                ->with('status', 'Member record created — collect payment to activate coverage.');
        }

        $membership = $this->enrollmentService->enrollRetailWithManualPayment($validated, $interval);

        return redirect()
            ->route('admin.enrollment.index')
            ->with('walk_in_enrollment_result', [
                'membership_id' => $membership->id,
                'membership_number' => $membership->membership_number,
                'member_name' => trim($validated['first_name'].' '.$validated['last_name']),
                'email' => $validated['email'],
                'payment_method' => 'manual',
            ])
            ->with('status', 'Walk-in membership created without online payment.');
    }

    public function checkout(Request $request, string $token): View|RedirectResponse
    {
        abort_unless($request->user()?->hasAnyRole(['admin', 'dispatch']), 403);
        abort_unless(UsaPaymentsMembershipCheckoutService::isEnabled(), 503);

        $payload = Cache::get(self::CHECKOUT_CACHE_PREFIX.$token);
        if (! is_array($payload) || (int) ($payload['admin_user_id'] ?? 0) !== (int) $request->user()->id) {
            return redirect()
                ->route('admin.enrollment.index')
                ->withErrors(['checkout' => 'This checkout link has expired. Start a new walk-in enrollment.']);
        }

        $membership = Membership::query()
            ->with(['plan', 'members'])
            ->find($payload['membership_id'] ?? null);
        $plan = Plan::query()->where('active', true)->find($payload['plan_id'] ?? null);
        $interval = (string) ($payload['interval'] ?? 'yearly');

        if (! $membership || ! $plan || ! $this->checkoutService->canCheckoutPlan($plan, $interval)) {
            return redirect()
                ->route('admin.enrollment.index')
                ->withErrors(['checkout' => 'This checkout session is no longer valid.']);
        }

        $amounts = $this->checkoutService->checkoutAmounts($plan, $interval);
        abort_unless($amounts !== null, 422);

        $primary = $membership->members->firstWhere('is_primary', true) ?? $membership->members->first();

        return view('admin.enrollment.checkout', [
            'membership' => $membership,
            'plan' => $plan,
            'interval' => $interval,
            'intervalLabel' => $this->intervalLabel($plan, $interval),
            'amounts' => $amounts,
            'token' => $token,
            'primary' => $primary,
            'prefillEmail' => $primary?->email,
        ]);
    }

    public function submitCheckout(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->hasAnyRole(['admin', 'dispatch']), 403);
        abort_unless(UsaPaymentsMembershipCheckoutService::isEnabled(), 503);

        $validated = $request->validate([
            'token' => ['required', 'string', 'max:120'],
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

        $token = trim($validated['token']);
        $payload = Cache::pull(self::CHECKOUT_CACHE_PREFIX.$token);
        if (! is_array($payload) || (int) ($payload['admin_user_id'] ?? 0) !== (int) $request->user()->id) {
            abort(403, 'This checkout link has expired or was already used.');
        }

        $membership = Membership::query()
            ->with(['members', 'accountUser'])
            ->findOrFail($payload['membership_id']);
        $plan = Plan::query()->where('active', true)->findOrFail($payload['plan_id']);
        $interval = (string) $payload['interval'];
        $user = $membership->accountUser;

        abort_unless($user !== null, 422, 'The enrollment is missing a linked portal user.');

        $membership = $this->checkoutService->processCheckout(
            membership: $membership,
            plan: $plan,
            interval: $interval,
            user: $user,
            paymentToken: $validated['payment_token'],
            billingAddress: $validated,
        );

        return redirect()
            ->route('admin.enrollment.index')
            ->with('walk_in_enrollment_result', [
                'membership_id' => $membership->id,
                'membership_number' => $membership->membership_number,
                'member_name' => trim($validated['first_name'].' '.$validated['last_name']),
                'email' => $validated['email'],
                'payment_method' => 'usa_payments',
            ])
            ->with('status', 'Payment successful. Walk-in membership is now active.');
    }

    private function createCheckoutToken(Membership $membership, Plan $plan, string $interval, int $adminUserId): string
    {
        $token = Str::random(64);
        Cache::put(
            self::CHECKOUT_CACHE_PREFIX.$token,
            [
                'membership_id' => $membership->id,
                'plan_id' => $plan->id,
                'interval' => $interval,
                'admin_user_id' => $adminUserId,
            ],
            now()->addMinutes(30),
        );

        return $token;
    }

    private function resolveInterval(Plan $plan, string $requested): string
    {
        if ($plan->billing_interval === 'one_time') {
            return 'onetime';
        }

        if (in_array($requested, ['monthly', 'yearly'], true)) {
            return $requested;
        }

        if ($plan->billing_interval === 'monthly') {
            return 'monthly';
        }

        if ((float) ($plan->price_monthly ?? 0) > 0 && (float) ($plan->price ?? 0) <= 0) {
            return 'monthly';
        }

        return 'yearly';
    }

    private function intervalLabel(Plan $plan, string $interval): string
    {
        return match ($interval) {
            'monthly' => 'Monthly',
            'onetime' => 'One-time',
            default => 'Annual',
        };
    }
}
