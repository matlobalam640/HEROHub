<?php

namespace App\Services;

use App\Models\Membership;
use App\Models\User;
use App\Services\UsaPayments\UsaPaymentsSubscriptionService;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class UsaPaymentsPaymentMethodService
{
    public static function isEnabled(): bool
    {
        return UsaPaymentsMembershipCheckoutService::isEnabled();
    }

    public function updatePaymentMethod(Membership $membership, User $user, string $paymentToken): Membership
    {
        if ($membership->billing_provider !== 'usa_payments') {
            throw ValidationException::withMessages([
                'payment' => 'Online card updates are only available for USA Payments memberships.',
            ]);
        }

        $subscriptionId = trim((string) ($membership->billing_subscription_id ?? ''));
        if ($subscriptionId === '') {
            throw ValidationException::withMessages([
                'payment' => 'This membership has no billing subscription ID on file. Complete a renewal checkout first or contact support.',
            ]);
        }

        $gateway = app(UsaPaymentsSubscriptionService::class);
        $response = $gateway->updateSubscriptionPaymentMethod($subscriptionId, $paymentToken);
        $parsed = $response->getParsedResponse();

        if (! $response->isApproved()) {
            Log::warning('USA Payments payment method update declined.', [
                'membership_id' => $membership->id,
                'subscription_id' => $subscriptionId,
                'response_code' => $parsed['response_code'] ?? null,
            ]);

            throw ValidationException::withMessages([
                'payment' => $parsed['response_code_text'] ?: 'Could not update your card. Please check the details and try again.',
            ]);
        }

        $membership->update([
            'billing_auto_collect' => true,
            'auto_renew' => true,
        ]);

        return $membership->fresh(['plan']);
    }
}
