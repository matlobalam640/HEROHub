<?php

namespace App\Http\Controllers;

use App\Services\HeroPortal\HeroPortalWebhookService;
use App\Http\Services\UsaPayments\SubscriptionService;
use App\Http\Services\ZohoApi\ZohoService;
use App\Models\PaymentsLog;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class SubscriptionController extends Controller
{
    private SubscriptionService $cardsService;
    private ZohoService $zohoService;
    private HeroPortalWebhookService $heroPortalWebhookService;

    public function __construct()
    {
        $this->cardsService = new SubscriptionService();
        $this->zohoService = new ZohoService();
        $this->heroPortalWebhookService = new HeroPortalWebhookService();
    }

    public function getPlan($plan_id)
    {
        $plan = Plan::query()->where('plan_id', $plan_id)->firstOrFail();

        return view('subscribe', compact('plan'));
    }

    public function subscribeToPlan($plan_id, Request $request)
    {
        $request->validate([
            'first_name'    => 'required|string',
            'last_name'     => 'required|string',
            'email'         => 'required|email',
            'company'       => 'required|string',
            'industry'      => 'required|string',
            'phone'         => 'required|string',
            'state'         => 'required|string',
            'street'        => 'required|string',
            'city'          => 'required|string',
            'zip_code'      => 'required|string',
            'country'       => 'required|string',
            'payment_token' => 'required|string',
        ]);

        $plan = Plan::query()->where('plan_id', $plan_id)->firstOrFail();
        $ip   = $_SERVER["HTTP_CF_CONNECTING_IP"] ?? ($request->getClientIps()[0] ?? $request->ip());

        $response       = $this->cardsService->addSubscription($request->get('payment_token'), $plan->plan_id, $request->get('email'), $request->get('first_name'), $request->get('last_name'), $plan->plan_amount * 1.1, $plan->plan_amount * 0.1, $plan->plan_name);
        $parsedResponse = $response->getParsedResponse();

        PaymentsLog::query()->create([
            'plan_id'         => $plan->plan_id,
            'user_name'       => $request->get('first_name') . ' ' . $request->get('last_name'),
            'email'           => $request->get('email'),
            'amount'          => $plan->plan_amount,
            'response_code'   => $parsedResponse['response_code'] ?? '',
            'transactionid'   => $parsedResponse['transactionid'] ?? '',
            'subscription_id' => $parsedResponse['subscription_id'] ?? null,
            'response'        => $response->getRawResponse(),
            'ip'              => $ip,
        ]);

        if (($parsedResponse['response'] ?? null) !== "1") {
            return Redirect::back()->withErrors([
                'error' => $parsedResponse['response_code_text'] ?? 'Payment was declined.',
            ]);
        }

        $this->heroPortalWebhookService->notifySuccessfulSubscription(
            $plan,
            $request->only([
                'first_name',
                'last_name',
                'email',
                'phone',
                'street',
                'city',
                'state',
                'zip_code',
                'country',
            ]),
            $response,
        );

        $response = $this->zohoService->createLead(
            $request->get('company'),
            $request->get('industry'),
            $request->get('last_name'),
            $request->get('first_name'),
            $request->get('email'),
            $request->get('phone'),
            $request->get('state'),
            'Subscription Form',
            'New Membership',
            $plan->plan_name,
            $request->get('street'),
            $request->get('city'),
            $request->get('zip_code'),
            $request->get('country')
        );

        if ($response->getStatusCode() !== 201) {
            return Redirect::back()->withErrors([
                'error' => 'Unexpected error occurred. Please Contact support.',
            ]);
        }

        return view('success', compact('plan'));
    }
}
