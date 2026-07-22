<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

/**
 * Small business catalog from HERO Client Rescue S.A Small Business Plans 2025.
 * USD list prices; tax is handled outside seeded data.
 * For yearly plans, monthly values enforce a 20% annual-prepay discount:
 * yearly = monthly * 12 * 0.80.
 */
class SmallBusinessPlansSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            [
                'code' => 'HB-01',
                'name' => 'Workplace Coverage - On-Site',
                'category' => 'business',
                'tier' => 'workplace',
                'sort_order' => 10,
                'billing_interval' => 'yearly',
                'price' => 32.00,
                'price_monthly' => 3.33,
                'min_members' => 25,
                'max_members' => null,
                'features' => [
                    'ALS Standard EMS stand-by service',
                    'Emergency response plan',
                    'Workplace safety assessment',
                    'Access to purchase first-aid kits for workplace',
                    'Limited to one workplace location in metropolitan Port-au-Prince',
                ],
                'ideal_for' => 'Small businesses with 25+ employees needing on-site coverage',
                'included_members' => null,
                'addon_price_yearly' => null,
            ],
            [
                'code' => 'HB-02',
                'name' => 'Workplace Coverage - Mobile',
                'category' => 'business',
                'tier' => 'workplace',
                'sort_order' => 20,
                'billing_interval' => 'yearly',
                'price' => 62.50,
                'price_monthly' => 6.51,
                'min_members' => 25,
                'max_members' => null,
                'features' => [
                    'ALS Standard EMS stand-by service',
                    'Emergency response plan',
                    '1 in-country flight transport ($200 co-pay)',
                    '3rd-party injury coverage while on duty',
                    'Access to purchase first-aid kits for vehicles',
                ],
                'ideal_for' => 'Small business mobile teams such as drivers, sales crews, and field staff',
                'included_members' => null,
                'addon_price_yearly' => null,
            ],
            [
                'code' => 'HB-03A',
                'name' => 'Manager Plan - Individual',
                'category' => 'business',
                'tier' => 'manager',
                'sort_order' => 30,
                'billing_interval' => 'yearly',
                'price' => 118.75,
                'price_monthly' => 12.37,
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
                'code' => 'HB-03B',
                'name' => 'Manager Plan - Family',
                'category' => 'business',
                'tier' => 'manager',
                'sort_order' => 40,
                'billing_interval' => 'yearly',
                'price' => 200.00,
                'price_monthly' => 20.83,
                'min_members' => null,
                'max_members' => 6,
                'features' => [
                    'Access to HERO WhatsApp security groups',
                    '2 in-country emergency flight transports per family',
                    '24/7 unlimited access to HERO emergency resources',
                    '5 ground ambulance transports',
                    'Patient advocacy support for hospitalization',
                    '3rd-party injury coverage',
                    'Additional family members: $32.50/year each (up to 6 total)',
                ],
                'ideal_for' => 'Manager families needing annual coverage',
                'included_members' => 4,
                'addon_price_yearly' => 32.50,
            ],
            [
                'code' => 'HB-04A',
                'name' => 'Executive Plan - Individual',
                'category' => 'business',
                'tier' => 'executive',
                'sort_order' => 50,
                'billing_interval' => 'yearly',
                'price' => 312.50,
                'price_monthly' => 32.55,
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
                'code' => 'HB-04B',
                'name' => 'Executive Plan - Family',
                'category' => 'business',
                'tier' => 'executive',
                'sort_order' => 60,
                'billing_interval' => 'yearly',
                'price' => 500.00,
                'price_monthly' => 52.08,
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
                    'Additional family members: $122/year each (up to 6 total)',
                ],
                'ideal_for' => 'Executive families requiring premium annual coverage',
                'included_members' => 4,
                'addon_price_yearly' => 122.00,
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
