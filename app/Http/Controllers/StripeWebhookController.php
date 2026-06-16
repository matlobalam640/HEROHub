<?php

namespace App\Http\Controllers;

use App\Services\StripeMembershipPlanChangeCheckoutService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Stripe\Exception\SignatureVerificationException;

class StripeWebhookController extends Controller
{
    public function handle(Request $request, StripeMembershipPlanChangeCheckoutService $checkoutService): Response
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature', '');

        try {
            $event = $checkoutService->verifyWebhookSignature($payload, $sigHeader);
        } catch (SignatureVerificationException) {
            return response('', 400);
        } catch (\Throwable) {
            return response('', 400);
        }

        if ($event->type === 'checkout.session.completed') {
            $object = $event->data->object ?? null;
            if (is_object($object) && isset($object->id)) {
                $checkoutService->finalizePaidCheckoutSession((string) $object->id, null);
            }
        }

        return response('', 200);
    }
}
