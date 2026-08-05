<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Stripe (membership plan change checkout)
    |--------------------------------------------------------------------------
    |
    | When STRIPE_SECRET is set, upgrade/downgrade uses Stripe Checkout before
    | updating the portal membership billing metadata.
    |
    */

    'secret' => env('STRIPE_SECRET'),

    'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),

];
