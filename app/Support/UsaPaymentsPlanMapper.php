<?php

namespace App\Support;

class UsaPaymentsPlanMapper
{
    /**
     * Map a USA Payments / gateway plan_id to a HEROHub plans.code value.
     */
    public static function portalPlanCodeFromGatewayPlanId(string $gatewayPlanId): ?string
    {
        $gatewayPlanId = trim($gatewayPlanId);
        if ($gatewayPlanId === '') {
            return null;
        }

        $reverse = config('usa_payments.gateway_to_portal', []);

        if (isset($reverse[$gatewayPlanId])) {
            return $reverse[$gatewayPlanId];
        }

        foreach (config('usa_payments.plan_ids', []) as $portalCode => $intervals) {
            if (! is_array($intervals)) {
                continue;
            }
            foreach ($intervals as $mappedId) {
                if ((string) $mappedId === $gatewayPlanId) {
                    return (string) $portalCode;
                }
            }
        }

        return null;
    }

    /**
     * Resolve portal plan code from webhook payload fields.
     *
     * @param  array<string, mixed>  $payload
     */
    public static function portalPlanCodeFromWebhookPayload(array $payload): ?string
    {
        foreach ([
            'plan_code',
            'gateway_plan_code',
            'membership_plan_code',
            'plan_identifier',
            'product_code',
            'sku',
        ] as $key) {
            $value = trim((string) ($payload[$key] ?? ''));
            if ($value !== '') {
                return $value;
            }
        }

        $payment = self::arrayOrDecode($payload['payment'] ?? null);
        if (is_array($payment)) {
            foreach (['gateway_plan_id', 'plan_id', 'plan_code'] as $key) {
                $gatewayId = trim((string) ($payment[$key] ?? ''));
                if ($gatewayId !== '') {
                    $mapped = self::portalPlanCodeFromGatewayPlanId($gatewayId);
                    if ($mapped !== null) {
                        return $mapped;
                    }
                }
            }
        }

        foreach (['gateway_plan_id', 'plan_id'] as $key) {
            $gatewayId = trim((string) ($payload[$key] ?? ''));
            if ($gatewayId !== '') {
                $mapped = self::portalPlanCodeFromGatewayPlanId($gatewayId);
                if ($mapped !== null) {
                    return $mapped;
                }
            }
        }

        $lineItems = self::arrayOrDecode($payload['line_items'] ?? null);
        if (is_array($lineItems) && isset($lineItems[0]) && is_array($lineItems[0])) {
            foreach (['code', 'plan_code', 'product_code', 'sku', 'gateway_plan_id', 'plan_id'] as $key) {
                $value = trim((string) ($lineItems[0][$key] ?? ''));
                if ($value === '') {
                    continue;
                }
                $mapped = self::portalPlanCodeFromGatewayPlanId($value) ?? $value;

                return $mapped;
            }
        }

        $plan = self::arrayOrDecode($payload['plan'] ?? null);
        if (is_array($plan)) {
            foreach (['plan_code', 'code', 'product_code', 'sku', 'gateway_plan_id', 'plan_id'] as $key) {
                $value = trim((string) ($plan[$key] ?? ''));
                if ($value === '') {
                    continue;
                }

                return self::portalPlanCodeFromGatewayPlanId($value) ?? $value;
            }
        }

        return null;
    }

    /**
     * @return array<string, mixed>|null
     */
    private static function arrayOrDecode(mixed $value): ?array
    {
        if (is_array($value)) {
            return $value;
        }
        if (! is_string($value)) {
            return null;
        }
        $trim = trim($value);
        if ($trim === '' || ($trim[0] !== '{' && $trim[0] !== '[')) {
            return null;
        }
        $decoded = json_decode($trim, true);

        return json_last_error() === JSON_ERROR_NONE && is_array($decoded) ? $decoded : null;
    }
}
