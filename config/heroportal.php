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
    | Zoho Billing customer portal (optional)
    |--------------------------------------------------------------------------
    |
    | Shown on membership plan-change checkout so members can cancel an old
    | subscription when replacing it (no cancel API is wired in this app).
    |
    */

    'zoho_customer_portal_url' => env('ZOHO_CUSTOMER_PORTAL_URL'),

    /*
    |--------------------------------------------------------------------------
    | Zoho CRM Deluge functions (portal integration)
    |--------------------------------------------------------------------------
    |
    | Each CRM function can have its own Zoho API key (zapikey). Keys map to rows
    | in zoho_crm_function_endpoints by slug. Set each ZOHO_CRM_ZAPIKEY_* in .env
    | on the server only — never commit real values.
    |
    */

    'zoho_crm_function_api_keys' => [
        'create_subscription_from_portal' => env('ZOHO_CRM_ZAPIKEY_CREATE_SUBSCRIPTION'),
        'update_subscription_from_portal' => env('ZOHO_CRM_ZAPIKEY_UPDATE_SUBSCRIPTION'),
        'cancel_subscription_from_portal' => env('ZOHO_CRM_ZAPIKEY_CANCEL_SUBSCRIPTION'),
        'get_current_subscription_status' => env('ZOHO_CRM_ZAPIKEY_GET_CURRENT_SUBSCRIPTION_STATUS'),
        'get_subscription_related_invoices' => env('ZOHO_CRM_ZAPIKEY_GET_SUBSCRIPTION_RELATED_INVOICES'),
        'get_subscription_related_payments' => env('ZOHO_CRM_ZAPIKEY_GET_SUBSCRIPTION_RELATED_PAYMENTS'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Zoho Billing → portal webhook
    |--------------------------------------------------------------------------
    |
    | POST JSON subscription payloads to /api/v1/webhooks/zoho/subscription with
    | header X-Hero-Zoho-Webhook-Secret (or Authorization: Bearer <secret>).
    |
    */

    'zoho_webhook_secret' => env('ZOHO_WEBHOOK_SECRET'),

    'zoho_webhook_auto_create_users' => (bool) env('ZOHO_WEBHOOK_AUTO_CREATE_USERS', false),

    /*
    |--------------------------------------------------------------------------
    | Zoho webhook → new membership email
    |--------------------------------------------------------------------------
    |
    | When a subscription webhook creates a new membership row (first time for
    | that billing_subscription_id), queue a welcome email with portal login
    | and next-step links. Disable if Zoho or another system sends onboarding.
    |
    */

    'zoho_webhook_new_membership_mail' => (bool) env('ZOHO_WEBHOOK_NEW_MEMBERSHIP_MAIL', true),

];
