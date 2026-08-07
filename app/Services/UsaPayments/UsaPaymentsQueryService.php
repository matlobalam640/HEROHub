<?php

namespace App\Services\UsaPayments;

use Carbon\Carbon;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class UsaPaymentsQueryService
{
    /**
     * @return list<array<string, mixed>>
     */
    public function listRecurringSubscriptions(): array
    {
        $xml = $this->query(['report_type' => 'recurring']);
        if ($xml === null) {
            return [];
        }

        $subscriptions = [];
        foreach ($xml->subscription ?? [] as $subscription) {
            $parsed = $this->parseSubscriptionNode($subscription);
            if ($parsed !== null) {
                $subscriptions[] = $parsed;
            }
        }

        return $subscriptions;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getRecurringSubscription(string $subscriptionId): ?array
    {
        $subscriptionId = trim($subscriptionId);
        if ($subscriptionId === '') {
            return null;
        }

        $xml = $this->query([
            'report_type' => 'recurring',
            'subscription_id' => $subscriptionId,
        ]);
        if ($xml === null || ! isset($xml->subscription)) {
            return null;
        }

        return $this->parseSubscriptionNode($xml->subscription);
    }

    public function firstSuccessfulPaymentDate(string $subscriptionId): ?Carbon
    {
        $subscriptionId = trim($subscriptionId);
        if ($subscriptionId === '') {
            return null;
        }

        $xml = $this->query(['subscription_id' => $subscriptionId]);
        if ($xml === null) {
            return null;
        }

        $earliest = null;
        foreach ($xml->transaction ?? [] as $transaction) {
            foreach ($transaction->action ?? [] as $action) {
                if ((string) $action->success !== '1') {
                    continue;
                }

                $parsed = $this->parseGatewayTimestamp((string) $action->date);
                if ($parsed === null) {
                    continue;
                }

                if ($earliest === null || $parsed->lt($earliest)) {
                    $earliest = $parsed->copy();
                }
            }
        }

        return $earliest?->startOfDay();
    }

    public function lastSuccessfulPaymentDate(string $subscriptionId): ?Carbon
    {
        $subscriptionId = trim($subscriptionId);
        if ($subscriptionId === '') {
            return null;
        }

        $xml = $this->query(['subscription_id' => $subscriptionId]);
        if ($xml === null) {
            return null;
        }

        $latest = null;
        foreach ($xml->transaction ?? [] as $transaction) {
            foreach ($transaction->action ?? [] as $action) {
                if ((string) $action->success !== '1') {
                    continue;
                }

                $parsed = $this->parseGatewayTimestamp((string) $action->date);
                if ($parsed === null) {
                    continue;
                }

                if ($latest === null || $parsed->gt($latest)) {
                    $latest = $parsed->copy();
                }
            }
        }

        return $latest?->startOfDay();
    }

    /**
     * @param  \SimpleXMLElement  $subscription
     * @return array<string, mixed>|null
     */
    private function parseSubscriptionNode(\SimpleXMLElement $subscription): ?array
    {
        $subscriptionId = trim((string) ($subscription->subscription_id ?? ''));
        if ($subscriptionId === '') {
            return null;
        }

        $nextCharge = $this->parseDate((string) ($subscription->next_charge_date ?? ''));
        $planId = trim((string) ($subscription->plan->plan_id ?? ''));
        $planName = trim((string) ($subscription->plan->plan_name ?? ''));
        $dayFrequency = (int) ($subscription->plan->day_frequency ?? 0);

        return [
            'subscription_id' => $subscriptionId,
            'email' => strtolower(trim((string) ($subscription->email ?? ''))),
            'first_name' => trim((string) ($subscription->first_name ?? '')),
            'last_name' => trim((string) ($subscription->last_name ?? '')),
            'gateway_plan_id' => $planId,
            'plan_name' => $planName,
            'day_frequency' => $dayFrequency,
            'next_charge_date' => $nextCharge,
            'completed_payments' => (int) ($subscription->completed_payments ?? 0),
            'attempted_payments' => (int) ($subscription->attempted_payments ?? 0),
        ];
    }

    /**
     * @param  array<string, mixed>  $params
     */
    private function query(array $params): ?\SimpleXMLElement
    {
        $securityKey = config('usa_payments.security_key');
        if (! is_string($securityKey) || $securityKey === '') {
            return null;
        }

        try {
            $response = Http::asForm()
                ->timeout(60)
                ->post(config('usa_payments.query_url'), array_merge($params, [
                    'security_key' => $securityKey,
                ]));
        } catch (ConnectionException) {
            return null;
        }

        if (! $response->successful()) {
            return null;
        }

        $body = trim($response->body());
        if ($body === '' || ! str_starts_with($body, '<?xml')) {
            return null;
        }

        $xml = simplexml_load_string($body, 'SimpleXMLElement', LIBXML_NOCDATA);

        return $xml === false ? null : $xml;
    }

    private function parseDate(string $value): ?Carbon
    {
        $value = trim($value);
        if ($value === '' || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return null;
        }

        $year = (int) substr($value, 0, 4);
        if ($year < 2000 || $year > 2100) {
            return null;
        }

        try {
            return Carbon::parse($value)->startOfDay();
        } catch (\Throwable) {
            return null;
        }
    }

    private function parseGatewayTimestamp(string $value): ?Carbon
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        if (preg_match('/^\d{14}$/', $value)) {
            $parsed = Carbon::createFromFormat('YmdHis', $value);

            return $parsed instanceof Carbon ? $parsed : null;
        }

        if (preg_match('/^\d{8}$/', $value)) {
            $parsed = Carbon::createFromFormat('Ymd', $value);

            return $parsed instanceof Carbon ? $parsed->startOfDay() : null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }
}
