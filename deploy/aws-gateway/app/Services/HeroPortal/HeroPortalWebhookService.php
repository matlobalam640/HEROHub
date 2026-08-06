<?php

namespace App\Services\HeroPortal;

use App\Http\Services\UsaPayments\UsaPaymentResponse;
use App\Models\Plan;
use Carbon\Carbon;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HeroPortalWebhookService
{
    public function notifySuccessfulSubscription(
        Plan $plan,
        array $requestData,
        UsaPaymentResponse $response,
    ): void {
        $url = config('heroportal.webhook_url');
        $secret = config('heroportal.webhook_secret');

        if (! is_string($url) || $url === '' || ! is_string($secret) || $secret === '') {
            Log::warning('HERO portal webhook skipped: HERO_PORTAL_WEBHOOK_URL or HERO_WEBHOOK_SECRET is not configured.');

            return;
        }

        $parsed = $response->getParsedResponse();
        $subscriptionId = trim((string) ($parsed['subscription_id'] ?? ''));
        if ($subscriptionId === '') {
            $subscriptionId = trim((string) ($parsed['transactionid'] ?? ''));
        }
        if ($subscriptionId === '') {
            Log::warning('HERO portal webhook skipped: missing subscription_id and transactionid in gateway response.');

            return;
        }

        $payload = $this->buildPayload($plan, $requestData, $parsed, $subscriptionId);

        try {
            $result = Http::timeout(20)
                ->acceptJson()
                ->withHeaders(['X-Hero-Webhook-Secret' => $secret])
                ->post($url, $payload);

            if (! $result->successful()) {
                Log::error('HERO portal webhook returned an error.', [
                    'status' => $result->status(),
                    'body' => $result->body(),
                ]);
            }
        } catch (ConnectionException $e) {
            Log::error('HERO portal webhook connection failed.', [
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $requestData
     * @param  array<string, mixed>  $parsed
     * @return array<string, mixed>
     */
    private function buildPayload(Plan $plan, array $requestData, array $parsed, string $subscriptionId): array
    {
        $start = now()->startOfDay();
        $end = $this->coverageEnd($start, $plan);
        $base = (float) $plan->plan_amount;
        $tax = round($base * 0.10, 2);
        $total = round($base + $tax, 2);

        return [
            'subscription_id' => $subscriptionId,
            'status' => 'live',
            'billing_provider' => 'usa_payments',
            'gateway_plan_id' => $plan->plan_id,
            'plan_code' => HeroPortalPlanMapper::toPortalPlanCode($plan->plan_id),
            'start_date' => $start->toDateString(),
            'current_term_ends_at' => $end->toDateString(),
            'next_billing_at' => $end->toDateString(),
            'last_billing_at' => $start->toDateString(),
            'auto_collect' => 'true',
            'customer' => [
                'email' => (string) ($requestData['email'] ?? ''),
                'display_name' => trim(((string) ($requestData['first_name'] ?? '')).' '.((string) ($requestData['last_name'] ?? ''))),
                'phone' => (string) ($requestData['phone'] ?? ''),
                'street' => (string) ($requestData['street'] ?? ''),
                'city' => (string) ($requestData['city'] ?? ''),
                'state' => (string) ($requestData['state'] ?? ''),
                'zip_code' => (string) ($requestData['zip_code'] ?? ''),
                'country' => (string) ($requestData['country'] ?? ''),
            ],
            'payment' => [
                'transaction_id' => (string) ($parsed['transactionid'] ?? ''),
                'subscription_id' => (string) ($parsed['subscription_id'] ?? $subscriptionId),
                'auth_code' => (string) ($parsed['authcode'] ?? ''),
                'response_code' => (string) ($parsed['response_code'] ?? ''),
                'amount' => $total,
                'tax' => $tax,
                'currency' => 'USD',
                'gateway_plan_id' => $plan->plan_id,
            ],
        ];
    }

    private function coverageEnd(Carbon $start, Plan $plan): Carbon
    {
        $frequency = (int) ($plan->day_frequency ?? 0);

        if ($frequency === 365) {
            return $start->copy()->addYear();
        }

        if ($frequency === 30) {
            return $start->copy()->addMonth();
        }

        $name = strtolower((string) $plan->plan_name);
        if (str_contains($name, '10 day')) {
            return $start->copy()->addDays(10);
        }

        if (str_contains($name, '1 month') || str_contains($name, '1-month')) {
            return $start->copy()->addDays(30);
        }

        return $start->copy()->addYear();
    }
}
