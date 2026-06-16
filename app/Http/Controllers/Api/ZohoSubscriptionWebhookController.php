<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Zoho\ZohoSubscriptionWebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

class ZohoSubscriptionWebhookController extends Controller
{
    public function __construct(
        private readonly ZohoSubscriptionWebhookService $zohoSubscriptionWebhookService
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $payload = $request->all();
        if ($payload === []) {
            return response()->json(['message' => 'Empty JSON body.'], 422);
        }

        try {
            $result = $this->zohoSubscriptionWebhookService->sync($payload);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (Throwable $e) {
            report($e);

            return response()->json(['message' => 'Server error while syncing subscription.'], 500);
        }

        $m = $result['membership'];

        return response()->json([
            'ok' => true,
            'membership_id' => $m->id,
            'membership_number' => $m->membership_number,
            'created' => $result['created'],
            'user_id' => $result['user']?->id,
            'plan_code' => $m->plan?->code,
            'billing_subscription_created_at' => $m->billing_subscription_created_at?->toIso8601String(),
            'billing_next_billing_at' => $m->billing_next_billing_at?->toDateString(),
            'billing_last_billing_at' => $m->billing_last_billing_at?->toDateString(),
            'billing_auto_collect' => $m->billing_auto_collect,
        ]);
    }
}
