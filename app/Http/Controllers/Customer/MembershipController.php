<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Membership;
use App\Models\Plan;
use App\Services\StripeMembershipPlanChangeCheckoutService;
use App\Support\MembershipCardPresenter;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class MembershipController extends Controller
{
    public function show(Request $request)
    {
        $membership = $this->membershipFor($request);

        $card = $membership ? MembershipCardPresenter::from($membership) : null;

        return view('customer.membership.show', [
            'membership' => $membership,
            'card' => $card,
        ]);
    }

    public function downloadCard(Request $request)
    {
        $membership = Membership::query()
            ->with(['plan', 'members'])
            ->where('account_user_id', $request->user()->id)
            ->orderByDesc('id')
            ->first();

        if (! $membership) {
            abort(404);
        }

        $card = MembershipCardPresenter::from($membership);

        $pdf = Pdf::loadView('customer.membership.card-pdf', ['card' => $card]);
        $pdf->setPaper('a4', 'portrait');

        $safeId = preg_replace('/[^A-Za-z0-9_-]+/', '-', $membership->membership_number) ?: 'membership';

        return $pdf->download('HERO-membership-card-'.$safeId.'.pdf');
    }

    public function updatePlan(Request $request): RedirectResponse
    {
        $membership = $this->membershipFor($request);
        abort_unless($membership, 404);

        $validated = $request->validate([
            'plan_id' => ['required', 'exists:plans,id'],
            'interval' => ['nullable', 'in:monthly,yearly'],
        ]);

        $plan = Plan::query()->findOrFail((int) $validated['plan_id']);
        $interval = $validated['interval'] ?? null;
        if (! in_array($interval, ['monthly', 'yearly'], true)) {
            $interval = $membership->plan?->billing_interval === 'monthly' ? 'monthly' : 'yearly';
        }

        if ($plan->id === $membership->plan_id) {
            return back()->with('status', 'This is already your active plan.');
        }

        $redirect = $this->redirectToStripePlanReviewIfEligible($request, $membership, $plan, $interval);
        if ($redirect !== null) {
            return $redirect;
        }

        $membership->update(['plan_id' => $plan->id]);

        return back()->with(
            'status',
            StripeMembershipPlanChangeCheckoutService::isEnabled()
                ? 'Plan updated in the portal only. This plan has no Stripe price for the selected billing cycle — set catalog prices (USD) or pick another interval.'
                : 'Plan updated in the portal. Card checkout is disabled — set STRIPE_SECRET in your environment to collect payment when changing plans.'
        );
    }

    /**
     * Start Stripe plan change from catalog “Subscribe” links (same flow as the plan picker).
     */
    public function subscribeFromCatalog(Request $request, Plan $plan, string $interval): RedirectResponse
    {
        abort_unless($request->user()->hasAnyRole(['customer', 'business']), 403);

        $membership = $this->membershipFor($request);
        if (! $membership) {
            return redirect()->route('portal.plans.retail')
                ->with('status', 'You need an active membership on your account to subscribe from the catalog.');
        }

        abort_unless($plan->active, 404);

        $interval = strtolower($interval);
        if (! in_array($interval, ['monthly', 'yearly'], true)) {
            abort(404);
        }

        if ($plan->id === $membership->plan_id) {
            return redirect()->route('customer.membership.plan')
                ->with('status', 'This is already your active plan.');
        }

        $redirect = $this->redirectToStripePlanReviewIfEligible($request, $membership, $plan, $interval);
        if ($redirect !== null) {
            return $redirect;
        }

        return redirect()->route('portal.plans.retail')
            ->with('status', 'Online payment is not available for this plan or billing cycle. Enable Stripe (STRIPE_SECRET) and ensure the plan has a USD price.');
    }

    private function redirectToStripePlanReviewIfEligible(Request $request, Membership $membership, Plan $plan, string $interval): ?RedirectResponse
    {
        if (! StripeMembershipPlanChangeCheckoutService::isEnabled()) {
            return null;
        }

        $interval = strtolower($interval);
        if (! in_array($interval, ['monthly', 'yearly'], true)) {
            return null;
        }

        if ($plan->unitAmountUsdForStripePlanChange($interval) === null) {
            return null;
        }

        $token = Str::random(64);
        Cache::put(
            StripeMembershipPlanChangeCheckoutService::REVIEW_CACHE_PREFIX.$token,
            [
                'membership_id' => $membership->id,
                'plan_id' => $plan->id,
                'interval' => $interval,
                'user_id' => $request->user()->id,
            ],
            now()->addMinutes(20)
        );

        return redirect()->route('customer.membership.plan.stripe.review', ['token' => $token]);
    }

    public function plan(Request $request)
    {
        $membership = $this->membershipFor($request);
        abort_unless($membership, 404);

        $availablePlans = Plan::query()
            ->where('active', true)
            ->orderBy('category')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $stripePlanCheckoutEnabled = StripeMembershipPlanChangeCheckoutService::isEnabled();

        return view('customer.membership.plan', [
            'membership' => $membership,
            'availablePlans' => $availablePlans,
            'stripePlanCheckoutEnabled' => $stripePlanCheckoutEnabled,
        ]);
    }

    public function updateBilling(Request $request): RedirectResponse
    {
        $membership = $this->membershipFor($request);
        abort_unless($membership, 404);

        $validated = $request->validate([
            'billing_provider' => ['nullable', 'in:stripe,zoho,manual'],
            'billing_customer_id' => ['nullable', 'string', 'max:120'],
        ]);

        $membership->update($validated);

        return back()->with('status', 'Payment method updated.');
    }

    public function updateAutoRenew(Request $request): RedirectResponse
    {
        $membership = $this->membershipFor($request);
        abort_unless($membership, 404);

        $validated = $request->validate([
            'auto_renew' => ['required', 'boolean'],
        ]);

        $membership->update(['auto_renew' => (bool) $validated['auto_renew']]);

        return back()->with('status', 'Auto-renew settings saved.');
    }

    public function storeDependent(Request $request): RedirectResponse
    {
        $membership = $this->membershipFor($request);
        abort_unless($membership, 404);
        abort_unless($membership->plan?->allowsFamilyDependents(), 403);

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:80'],
            'last_name' => ['required', 'string', 'max:80'],
            'relationship' => ['nullable', 'string', 'max:80'],
            'phone' => ['nullable', 'string', 'max:30'],
        ]);

        $membership->dependents()->create($validated);

        return back()->with('status', 'Family member added.');
    }

    public function destroyDependent(Request $request, int $dependentId): RedirectResponse
    {
        $membership = $this->membershipFor($request);
        abort_unless($membership, 404);

        $dependent = $membership->dependents()
            ->where('id', $dependentId)
            ->where(function ($q) {
                $q->whereNull('relationship')
                    ->orWhere('relationship', '!=', 'visitor');
            })
            ->firstOrFail();

        $dependent->delete();

        return back()->with('status', 'Family member removed.');
    }

    public function storeVisitor(Request $request): RedirectResponse
    {
        $membership = $this->membershipFor($request);
        abort_unless($membership, 404);

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:80'],
            'last_name' => ['required', 'string', 'max:80'],
            'phone' => ['nullable', 'string', 'max:30'],
        ]);

        $membership->dependents()->create([
            ...$validated,
            'relationship' => 'visitor',
        ]);

        return back()->with('status', 'Short-term visitor coverage added.');
    }

    public function destroyVisitor(Request $request, int $dependentId): RedirectResponse
    {
        $membership = $this->membershipFor($request);
        abort_unless($membership, 404);

        $visitor = $membership->dependents()
            ->where('id', $dependentId)
            ->where('relationship', 'visitor')
            ->firstOrFail();

        $visitor->delete();

        return back()->with('status', 'Visitor coverage removed.');
    }

    public function downloadInvoice(Request $request, string $invoiceRef)
    {
        $membership = $this->membershipFor($request);
        abort_unless($membership, 404);

        $payments = $this->buildPaymentHistory($membership);
        $invoice = $payments->firstWhere('invoice', $invoiceRef);
        abort_unless($invoice, 404);

        $pdf = Pdf::loadView('customer.membership.invoice-pdf', [
            'membership' => $membership,
            'invoice' => $invoice,
        ]);
        $pdf->setPaper('a4', 'portrait');

        return $pdf->download('HERO-invoice-'.$invoiceRef.'.pdf');
    }

    public function payments(Request $request)
    {
        $membership = $this->membershipFor($request);
        abort_unless($membership, 404);

        $payments = $this->buildPaymentHistory($membership);

        return view('customer.membership.payments', [
            'membership' => $membership,
            'payments' => $payments,
        ]);
    }

    public function visitorCoverage(Request $request)
    {
        $membership = $this->membershipFor($request);
        abort_unless($membership, 404);

        $visitors = $membership->dependents
            ->where('relationship', 'visitor')
            ->values();

        return view('customer.membership.visitor-coverage', [
            'membership' => $membership,
            'visitors' => $visitors,
        ]);
    }

    public function familyMembers(Request $request)
    {
        $membership = $this->membershipFor($request);
        abort_unless($membership, 404);

        $familyDependents = $membership->dependents
            ->filter(fn ($dep) => $dep->relationship !== 'visitor')
            ->values();

        $canManageFamilyDependents = $membership->plan?->allowsFamilyDependents() ?? false;

        return view('customer.membership.family-members', [
            'membership' => $membership,
            'familyDependents' => $familyDependents,
            'canManageFamilyDependents' => $canManageFamilyDependents,
        ]);
    }

    public function paymentMethod(Request $request)
    {
        $membership = $this->membershipFor($request);
        abort_unless($membership, 404);

        return view('customer.membership.payment-method', [
            'membership' => $membership,
        ]);
    }

    private function membershipFor(Request $request): ?Membership
    {
        return Membership::query()
            ->with(['plan', 'members', 'dependents', 'company'])
            ->where('account_user_id', $request->user()->id)
            ->orderByDesc('id')
            ->first();
    }

    private function buildPaymentHistory(Membership $membership): Collection
    {
        $amount = (float) ($membership->plan?->price_monthly ?? $membership->plan?->price ?? 0);
        if ($amount <= 0) {
            $amount = 49;
        }

        return collect(range(0, 5))->map(function (int $monthOffset) use ($membership, $amount) {
            $period = now()->startOfMonth()->subMonths($monthOffset);

            return [
                'invoice' => sprintf('INV-%s-%s', $membership->id, $period->format('Ym')),
                'period' => $period->format('M Y'),
                'paid_at' => $period->copy()->addDays(2)->toDateString(),
                'amount' => number_format($amount, 2),
                'status' => 'paid',
            ];
        });
    }
}
