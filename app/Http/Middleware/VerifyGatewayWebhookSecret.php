<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyGatewayWebhookSecret
{
    public function handle(Request $request, Closure $next): Response
    {
        $secret = config('heroportal.webhook_secret');
        if (! is_string($secret) || $secret === '') {
            abort(Response::HTTP_SERVICE_UNAVAILABLE, 'Webhook secret is not configured.');
        }

        $token = $request->header('X-Hero-Webhook-Secret')
            ?? $request->bearerToken();

        if (! is_string($token) || ! hash_equals($secret, $token)) {
            abort(Response::HTTP_UNAUTHORIZED, 'Invalid webhook secret.');
        }

        return $next($request);
    }
}
