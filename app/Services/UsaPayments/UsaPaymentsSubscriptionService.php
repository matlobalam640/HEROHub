<?php

namespace App\Services\UsaPayments;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class UsaPaymentsSubscriptionService
{
    public function updateSubscriptionPaymentMethod(string $subscriptionId, string $paymentToken): UsaPaymentResponse
    {
        $response = Http::asForm()->post(config('usa_payments.transact_url'), [
            'security_key' => config('usa_payments.security_key'),
            'recurring' => 'update_subscription',
            'subscription_id' => $subscriptionId,
            'payment_token' => $paymentToken,
        ]);

        return UsaPaymentResponse::fromHttpResponse($response);
    }

    /**
     * @throws ConnectionException
     */
    public function addSubscription(
        string $token,
        string $planId,
        string $email,
        string $firstName,
        string $lastName,
        float $amount,
        float $tax,
        ?string $description = null,
    ): UsaPaymentResponse {
        $response = Http::asForm()->post(config('usa_payments.transact_url'), [
            'security_key' => config('usa_payments.security_key'),
            'type' => 'sale',
            'recurring' => 'add_subscription',
            'plan_id' => $planId,
            'payment_token' => $token,
            'amount' => round($amount, 2),
            'tax' => round($tax, 2),
            'order_description' => $description,
            'email' => $email,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'customer_receipt' => true,
        ]);

        return UsaPaymentResponse::fromHttpResponse($response);
    }
}
