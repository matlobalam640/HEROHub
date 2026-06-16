<?php

namespace App\Services;

use App\Models\Membership;
use App\Models\Plan;
use App\Models\ZohoCrmFunctionEndpoint;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ZohoCrmCreateSubscriptionFromPortalNotifier
{
    /**
     * POST JSON payload to Zoho CRM "create subscription from portal" function.
     * Adjust your Deluge script to read this body shape if needed.
     */
    public function notify(
        Membership $membership,
        Plan $plan,
        string $interval,
        string $stripeCheckoutSessionId,
        ?int $amountTotalCents
    ): void {
        $url = ZohoCrmFunctionEndpoint::signedUrlForSlug(ZohoCrmFunctionEndpoint::SLUG_CREATE_SUBSCRIPTION);
        if (! $url) {
            Log::warning('Zoho create_subscription_from_portal URL or zapikey not configured.');

            return;
        }

        $user = $membership->accountUser;
        $payload = [
            'membership_number' => $membership->membership_number,
            'membership_id' => $membership->id,
            'plan_id' => $plan->id,
            'plan_code' => $plan->code,
            'plan_name' => $plan->name,
            'billing_interval' => $interval,
            'stripe_checkout_session_id' => $stripeCheckoutSessionId,
            'amount_total_cents' => $amountTotalCents,
            'account_email' => $user?->email,
            'account_user_id' => $membership->account_user_id,
        ];

        try {
            $response = Http::timeout(60)
                ->acceptJson()
                ->asJson()
                ->post($url, $payload);

            if (! $response->successful()) {
                Log::error('Zoho create_subscription_from_portal non-success response.', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('Zoho create_subscription_from_portal request failed.', [
                'message' => $e->getMessage(),
            ]);
        }
    }
}
