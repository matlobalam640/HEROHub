<?php

return [

    /*
    |--------------------------------------------------------------------------
    | USA Payments (Collect.js + recurring subscriptions)
    |--------------------------------------------------------------------------
    |
    | Used for membership renewal and plan-change checkout in the customer portal.
    | Keys are the same as the payment-gateway Laravel app on AWS (EC2 plans table).
    |
    | Last verified against gateway DB: 2026-07-27
    |
    */

    'security_key' => env('USA_PAYMENTS_SECURITY_KEY'),

    'tokenization_key' => env('USA_DATA_TOKENIZATION_KEY'),

    'tax_rate' => (float) env('USA_PAYMENTS_TAX_RATE', 0.10),

    'transact_url' => env('USA_PAYMENTS_TRANSACT_URL', 'https://usapayments.transactiongateway.com/api/transact.php'),

    'collect_js_url' => env('USA_PAYMENTS_COLLECT_JS_URL', 'https://usapayments.transactiongateway.com/token/Collect.js'),

    /*
    |--------------------------------------------------------------------------
    | HEROHub plan code → USA Payments recurring plan_id
    |--------------------------------------------------------------------------
    |
    | Keys: HEROHub plans.code (RetailPlansSeeder).
    | Values: USA Payments plan_id strings from AWS gateway `plans.plan_id`.
    |
    | Interval keys:
    |   onetime — 10-day / 1-month retail plans (still use add_subscription on gateway)
    |   yearly  — annual prepay
    |   monthly — monthly billing
    |
    | Amounts below are pre-tax gateway list prices (portal adds 10% tax at checkout).
    */
    'plan_ids' => [
        // 10-day local ($25) → HR-01A
        'HR-01A' => ['onetime' => 'HR-01A'],

        // 10-day VIP ($41.50) → HR-01AC
        'HR-01AC' => ['onetime' => 'HR-01AC'],

        // 1-month local ($65) → HR-01B
        'HR-01B' => ['onetime' => 'HR-01B'],

        // 1-month VIP ($110.50) → HR-01C  (HEROHub code is HR-01BC)
        'HR-01BC' => ['onetime' => 'HR-01C'],

        // Annual individual local: yearly $199 / monthly $17
        'HR-02' => [
            'yearly' => 'HR-02Y',
            'monthly' => 'HR-02M',
        ],

        // Annual individual VIP: yearly $398.98 / monthly $33
        'HR-02C' => [
            'yearly' => 'HR-02CY',
            'monthly' => 'HR-02CM',
        ],

        // Annual family local (base, up to 4): yearly $325 / monthly $27
        'HR-03' => [
            'yearly' => 'HR-03-Y',
            'monthly' => 'HR-03-M',
        ],

        // Annual family VIP (base, up to 4): yearly $637 / monthly $62.50
        // NOTE: HR-03CM is "Individual VIP monthly" ($53) — wrong for family VIP.
        'HR-03C' => [
            'yearly' => 'HR-03CY',
            'monthly' => 'HR-03CM-5',
        ],
    ],

    'gateway_plans' => [
        'HR-01A' => ['name' => '10 Day Plan Local Individual', 'amount' => 25.00],
        'HR-01AC' => ['name' => '10 Day Plan Local VIP', 'amount' => 41.50],
        'HR-01B' => ['name' => '1 Month Local Plan', 'amount' => 65.00],
        'HR-01C' => ['name' => '1 Month VIP Plan', 'amount' => 110.50],
        'HR-02M' => ['name' => 'Individual Plan Monthly Payment', 'amount' => 17.00],
        'HR-02Y' => ['name' => 'Individual Plan Yearly Payment', 'amount' => 199.00],
        'HR-02CM' => ['name' => 'Individual Plan VIP Coverage Monthly Payment', 'amount' => 33.00],
        'HR-02CY' => ['name' => 'Individual Plan VIP Coverage Yearly Payment', 'amount' => 398.98],
        'HR-03-M' => ['name' => 'Family Plan Local Coverage Monthly Payment', 'amount' => 27.00],
        'HR-03-Y' => ['name' => 'Family Plan Local Coverage Yearly Payment', 'amount' => 325.00],
        'HR-03CM' => ['name' => 'Individual Plan VIP Coverage Monthly Payment', 'amount' => 53.00],
        'HR-03CY' => ['name' => 'Family Plan VIP Coverage Yearly Payment', 'amount' => 637.00],
        'HR-03-6M' => ['name' => '1-Year Local Plan Family of 6 Monthly Payment', 'amount' => 32.50],
        'HR-03-6Y' => ['name' => '1-Year Local Plan Family of 6 Yearly Payment', 'amount' => 390.00],
        'HR-03CM-5' => ['name' => '1-Year VIP Plan Family of 5 Monthly Payment', 'amount' => 62.50],
        'HR-03CM-6' => ['name' => '1-Year Local Plan Family of 6 Monthly Plan', 'amount' => 73.50],
        'HR-03CY-5' => ['name' => '1-Year VIP Plan Family of 5 Yearly Payment', 'amount' => 759.50],
        'HR-03CY-6' => ['name' => '1-Year VIP Plan Family of 6 Yearly Plan', 'amount' => 882.00],
        'HR-04M' => ['name' => '1-Year Local Plan Family of 5 Monthly Payment', 'amount' => 29.99],
        'HR-04Y' => ['name' => '1-Year Local Plan Family of 5 Yearly Payment', 'amount' => 357.00],
    ],

    /*
    | Gateway plan_id → HEROHub plans.code (used by subscription webhooks).
    */
    'gateway_to_portal' => [
        'HR-01A' => 'HR-01A',
        'HR-01AC' => 'HR-01AC',
        'HR-01B' => 'HR-01B',
        'HR-01C' => 'HR-01BC',
        'HR-02M' => 'HR-02',
        'HR-02Y' => 'HR-02',
        'HR-02CM' => 'HR-02C',
        'HR-02CY' => 'HR-02C',
        'HR-03-M' => 'HR-03',
        'HR-03-Y' => 'HR-03',
        'HR-03-6M' => 'HR-03',
        'HR-03-6Y' => 'HR-03',
        'HR-04M' => 'HR-03',
        'HR-04Y' => 'HR-03',
        'HR-03CM' => 'HR-02C',
        'HR-03CY' => 'HR-03C',
        'HR-03CM-5' => 'HR-03C',
        'HR-03CM-6' => 'HR-03C',
        'HR-03CY-5' => 'HR-03C',
        'HR-03CY-6' => 'HR-03C',
    ],

];
