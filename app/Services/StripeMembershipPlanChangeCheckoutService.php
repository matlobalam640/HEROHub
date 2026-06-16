<?php

namespace App\Services;

use App\Models\Membership;
use App\Models\MembershipPlanChangeStripeSession;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\Checkout\Session;
use Stripe\Event;
use Stripe\Stripe;
use Stripe\StripeClient;
use Stripe\Webhook;
use UnexpectedValueException;

class StripeMembershipPlanChangeCheckoutService
{
    public const REVIEW_CACHE_PREFIX = 'membership_plan_stripe_checkout:';

    public static function isEnabled(): bool
    {
        $secret = config('stripe.secret');

        return is_string($secret) && $secret !== '' && str_starts_with($secret, 'sk_');
    }

    public function createCheckoutSession(Membership $membership, Plan $plan, string $interval, User $user): Session
    {
        $interval = strtolower($interval);
        if (! in_array($interval, ['monthly', 'yearly'], true)) {
            abort(422, 'Invalid billing interval.');
        }

        $usd = $plan->unitAmountUsdForStripePlanChange($interval);
        if ($usd === null) {
            abort(422, 'This plan cannot be paid through Stripe for the selected interval.');
        }

        $unitAmount = (int) round($usd * 100);
        if ($unitAmount < 50) {
            abort(422, 'Amount is below the minimum Stripe can charge for this currency.');
        }

        $currency = strtolower((string) ($plan->currency ?: 'USD'));
        $intervalLabel = $interval === 'yearly' ? 'Annual' : 'Monthly';

        $stripe = $this->client();

        $session = $stripe->checkout->sessions->create([
            'mode' => 'payment',
            'customer_email' => $user->email,
            'line_items' => [
                [
                    'quantity' => 1,
                    'price_data' => [
                        'currency' => $currency,
                        'unit_amount' => $unitAmount,
                        'product_data' => [
                            'name' => $plan->name.' — '.$intervalLabel,
                            'metadata' => [
                                'plan_code' => (string) $plan->code,
                            ],
                        ],
                    ],
                ],
            ],
            'success_url' => route('customer.membership.plan.stripe.success', [], true).'?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('customer.membership.plan', [], true).'?stripe_checkout=cancelled',
            'metadata' => [
                'hero' => 'plan_change',
                'mid' => (string) $membership->id,
                'uid' => (string) $user->id,
                'pid' => (string) $plan->id,
                'iv' => $interval,
            ],
        ]);

        MembershipPlanChangeStripeSession::query()->create([
            'membership_id' => $membership->id,
            'plan_id' => $plan->id,
            'interval' => $interval,
            'stripe_checkout_session_id' => $session->id,
            'amount_total_cents' => $session->amount_total,
        ]);

        return $session;
    }

    /**
     * Apply paid plan change and notify Zoho. Safe to call from success redirect and webhook (idempotent).
     *
     * @param  int|null  $assertUserId  When set (browser success callback), metadata uid must match.
     */
    public function finalizePaidCheckoutSession(string $checkoutSessionId, ?int $assertUserId = null): bool
    {
        if (! static::isEnabled()) {
            return false;
        }

        $stripe = $this->client();
        $session = $stripe->checkout->sessions->retrieve($checkoutSessionId);

        if (($session->metadata['hero'] ?? null) !== 'plan_change') {
            return false;
        }

        if ($assertUserId !== null && (int) ($session->metadata['uid'] ?? 0) !== $assertUserId) {
            abort(403);
        }

        if ($session->payment_status !== 'paid') {
            return false;
        }

        $local = MembershipPlanChangeStripeSession::query()
            ->where('stripe_checkout_session_id', $checkoutSessionId)
            ->first();

        if (! $local) {
            Log::warning('Stripe plan change session missing local row.', ['checkout_session_id' => $checkoutSessionId]);

            return false;
        }

        return DB::transaction(function () use ($session, $local) {
            $locked = MembershipPlanChangeStripeSession::query()
                ->whereKey($local->id)
                ->lockForUpdate()
                ->first();

            if (! $locked || $locked->applied_at) {
                return true;
            }

            $membership = Membership::query()->whereKey($locked->membership_id)->lockForUpdate()->first();
            $plan = Plan::query()->whereKey($locked->plan_id)->first();
            if (! $membership || ! $plan) {
                return false;
            }

            $membership->update([
                'plan_id' => $plan->id,
                'billing_provider' => 'stripe',
                'billing_customer_id' => is_string($session->customer) ? $session->customer : null,
            ]);

            $locked->forceFill(['applied_at' => now()])->save();

            app(ZohoCrmCreateSubscriptionFromPortalNotifier::class)->notify(
                $membership->fresh(['plan']),
                $plan,
                $locked->interval,
                $checkoutSessionId,
                $session->amount_total !== null ? (int) $session->amount_total : null
            );

            return true;
        });
    }

    public function verifyWebhookSignature(string $payload, string $sigHeader): Event
    {
        $secret = config('stripe.webhook_secret');
        if (! is_string($secret) || $secret === '') {
            throw new UnexpectedValueException('Stripe webhook secret is not configured.');
        }

        return Webhook::constructEvent($payload, $sigHeader, $secret);
    }

    private function client(): StripeClient
    {
        $secret = config('stripe.secret');
        if (! is_string($secret) || $secret === '') {
            abort(500, 'Stripe is not configured.');
        }

        Stripe::setApiKey($secret);

        return new StripeClient($secret);
    }
}
