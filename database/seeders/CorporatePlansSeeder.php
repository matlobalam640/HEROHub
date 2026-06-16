<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

/**
 * Corporate catalog from HERO Client Rescue S.A Corporate Plans 2025.
 * USD list prices; tax is handled outside seeded data.
 */
class CorporatePlansSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            [
                'code' => 'HC-01',
                'name' => 'Workplace Coverage - On-Site',
                'category' => 'corporate',
                'tier' => 'workplace',
                'sort_order' => 10,
                'billing_interval' => 'yearly',
                'price' => 12.50,
                'price_monthly' => 1.05,
                'min_members' => 400,
                'max_members' => null,
                'features' => [
                    'ALS Standard EMS stand-by service',
                    'Emergency response plan',
                    'Workplace safety assessment',
                    'Access to purchase first-aid kits for workplace',
                    'Limited to one workplace location in metropolitan Port-au-Prince',
                ],
                'ideal_for' => 'Organizations with 400+ employees needing on-site coverage',
                'included_members' => null,
                'addon_price_yearly' => null,
            ],
            [
                'code' => 'HC-02',
                'name' => 'Workplace Coverage - Mobile',
                'category' => 'corporate',
                'tier' => 'workplace',
                'sort_order' => 20,
                'billing_interval' => 'yearly',
                'price' => 50.00,
                'price_monthly' => 4.17,
                'min_members' => 20,
                'max_members' => null,
                'features' => [
                    'ALS Standard EMS stand-by service',
                    'Emergency response plan',
                    '1 in-country flight transport ($200 co-pay)',
                    '3rd-party injury coverage while on duty',
                    'Access to purchase first-aid kits for vehicles',
                ],
                'ideal_for' => 'Mobile teams such as drivers, sales crews, and field staff',
                'included_members' => null,
                'addon_price_yearly' => null,
            ],
            [
                'code' => 'HC-03A',
                'name' => 'Manager Plan - Individual',
                'category' => 'corporate',
                'tier' => 'manager',
                'sort_order' => 30,
                'billing_interval' => 'yearly',
                'price' => 100.00,
                'price_monthly' => 8.35,
                'min_members' => null,
                'max_members' => null,
                'features' => [
                    'Access to HERO WhatsApp security groups',
                    '1 in-country emergency flight transport',
                    '24/7 unlimited access to HERO emergency resources',
                    '2 ground ambulance transports',
                    'Patient advocacy support for hospitalization',
                    '3rd-party injury coverage',
                ],
                'ideal_for' => 'Individual managers and supervisors',
                'included_members' => 1,
                'addon_price_yearly' => null,
            ],
            [
                'code' => 'HC-03B',
                'name' => 'Manager Plan - Family',
                'category' => 'corporate',
                'tier' => 'manager',
                'sort_order' => 40,
                'billing_interval' => 'yearly',
                'price' => 175.00,
                'price_monthly' => 14.60,
                'min_members' => null,
                'max_members' => 6,
                'features' => [
                    'Access to HERO WhatsApp security groups',
                    '2 in-country emergency flight transports per family',
                    '24/7 unlimited access to HERO emergency resources',
                    '5 ground ambulance transports',
                    'Patient advocacy support for hospitalization',
                    '3rd-party injury coverage',
                    'Additional family members: $25/year each (up to 6 total)',
                ],
                'ideal_for' => 'Manager families needing annual coverage',
                'included_members' => 4,
                'addon_price_yearly' => 25.00,
            ],
            [
                'code' => 'HC-04A',
                'name' => 'Executive Plan - Individual',
                'category' => 'corporate',
                'tier' => 'executive',
                'sort_order' => 50,
                'billing_interval' => 'yearly',
                'price' => 287.50,
                'price_monthly' => 23.95,
                'min_members' => null,
                'max_members' => null,
                'features' => [
                    'Access to HERO WhatsApp security groups',
                    '1 in-country emergency flight transport',
                    '24/7 unlimited access to HERO emergency resources',
                    '2 ground ambulance transports',
                    'Patient advocacy support for hospitalization',
                    '3rd-party injury coverage',
                    'VIP medevac coordination with International Concierge plan',
                ],
                'ideal_for' => 'Executives with enhanced emergency support requirements',
                'included_members' => 1,
                'addon_price_yearly' => null,
            ],
            [
                'code' => 'HC-04B',
                'name' => 'Executive Plan - Family',
                'category' => 'corporate',
                'tier' => 'executive',
                'sort_order' => 60,
                'billing_interval' => 'yearly',
                'price' => 468.75,
                'price_monthly' => 39.04,
                'min_members' => null,
                'max_members' => 6,
                'features' => [
                    'Access to HERO WhatsApp security groups',
                    '2 in-country emergency flight transports per family',
                    '24/7 unlimited access to HERO emergency resources',
                    '5 ground ambulance transports',
                    'Patient advocacy support for hospitalization',
                    '3rd-party injury coverage',
                    'VIP medevac coordination with International Concierge plan',
                    'Additional family members: $95/year each (up to 6 total)',
                ],
                'ideal_for' => 'Executive families requiring premium annual coverage',
                'included_members' => 4,
                'addon_price_yearly' => 95.00,
            ],
        ];

        foreach ($rows as $row) {
            Plan::updateOrCreate(
                ['code' => $row['code']],
                array_merge($row, [
                    'coverage_days' => null,
                    'commitment_months' => null,
                    'retail_subgroup' => null,
                    'currency' => 'USD',
                    'active' => true,
                ])
            );
        }
    }
}
