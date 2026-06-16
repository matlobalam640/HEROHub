<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Stripe (membership plan change checkout)
    |--------------------------------------------------------------------------
    |
    | When STRIPE_SECRET is set, upgrade/downgrade uses Stripe Checkout before
    | updating the portal and notifying Zoho CRM (create subscription function).
    |
    */

    'secret' => env('STRIPE_SECRET'),

    'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),

];
