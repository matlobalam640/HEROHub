<?php

namespace Database\Seeders;

use App\Models\ZohoCrmFunctionEndpoint;
use Illuminate\Database\Seeder;

class ZohoCrmFunctionEndpointsSeeder extends Seeder
{
    /**
     * Zoho CRM v7 function execute URLs (exact paths as in Zoho; zapikey is appended at runtime from .env per slug).
     */
    public function run(): void
    {
        $rows = [
            [
                'slug' => ZohoCrmFunctionEndpoint::SLUG_CREATE_SUBSCRIPTION,
                'label' => 'Create subscription from portal',
                'execute_url' => 'https://www.zohoapis.com/crm/v7/functions/create_subscription_from_portal/actions/execute',
            ],
            [
                'slug' => ZohoCrmFunctionEndpoint::SLUG_UPDATE_SUBSCRIPTION,
                'label' => 'Update subscription from portal',
                'execute_url' => 'https://www.zohoapis.com/crm/v7/functions/update_subscription_from_portal/actions/execute',
            ],
            [
                'slug' => ZohoCrmFunctionEndpoint::SLUG_CANCEL_SUBSCRIPTION,
                'label' => 'Cancel subscription from portal',
                'execute_url' => 'https://www.zohoapis.com/crm/v7/functions/cancel_subscription_from_portal/actions/execute',
            ],
            [
                'slug' => ZohoCrmFunctionEndpoint::SLUG_GET_CURRENT_SUBSCRIPTION_STATUS,
                'label' => 'Get current subscription status',
                'execute_url' => 'https://www.zohoapis.com/crm/v7/functions/get_current_subscription_status/actions/execute',
            ],
            [
                'slug' => ZohoCrmFunctionEndpoint::SLUG_GET_SUBSCRIPTION_RELATED_INVOICES,
                'label' => 'Get subscription-related invoices',
                'execute_url' => 'https://www.zohoapis.com/crm/v7/functions/get_subscription_related_invoices/actions/execute',
            ],
            [
                'slug' => ZohoCrmFunctionEndpoint::SLUG_GET_SUBSCRIPTION_RELATED_PAYMENTS,
                'label' => 'Get subscription-related payments',
                'execute_url' => 'https://www.zohoapis.com/crm/v7/functions/get_subscription_related_payments/actions/execute',
            ],
        ];

        foreach ($rows as $row) {
            ZohoCrmFunctionEndpoint::query()->updateOrCreate(
                ['slug' => $row['slug']],
                [
                    'execute_url' => $row['execute_url'],
                    'label' => $row['label'],
                ]
            );
        }
    }
}
