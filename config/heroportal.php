<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Digital membership card
    |--------------------------------------------------------------------------
    |
    | Shown on the customer portal and on downloadable PDFs.
    |
    */

    'membership_card' => [
        'company_name' => env('HERO_CARD_COMPANY_NAME', 'HERO Client Rescue S.A.'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Mail branding
    |--------------------------------------------------------------------------
    |
    | Optional absolute logo URL for email templates. Use this when the app
    | is behind private hosts or APP_URL is not publicly reachable.
    |
    */

    'mail_brand_logo_url' => env('HERO_MAIL_BRAND_LOGO_URL'),

    /*
    |--------------------------------------------------------------------------
    | Optional customer billing portal URL
    |--------------------------------------------------------------------------
    |
    | Shown on membership plan-change checkout so members can cancel an old
    | subscription when replacing it (no direct cancel API is wired in this app).
    |
    */

    'billing_customer_portal_url' => env('BILLING_CUSTOMER_PORTAL_URL'),

    /*
    |--------------------------------------------------------------------------
    | Website/payment-gateway → portal webhook
    |--------------------------------------------------------------------------
    |
    | POST JSON subscription payloads to /api/v1/webhooks/subscription with
    | header X-Hero-Webhook-Secret (or Authorization: Bearer <secret>).
    |
    */
    'webhook_secret' => env('HERO_WEBHOOK_SECRET'),
    'webhook_auto_create_users' => (bool) env('HERO_WEBHOOK_AUTO_CREATE_USERS', false),
    'webhook_new_membership_mail' => (bool) env('HERO_WEBHOOK_NEW_MEMBERSHIP_MAIL', true),

];
