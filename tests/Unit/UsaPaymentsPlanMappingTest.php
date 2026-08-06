<?php

namespace Tests\Unit;

use App\Models\Plan;
use App\Services\UsaPaymentsMembershipCheckoutService;
use Tests\TestCase;

class UsaPaymentsPlanMappingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'usa_payments.security_key' => 'test-key',
            'usa_payments.tokenization_key' => 'test-token',
        ]);
    }

    /**
     * @dataProvider retailPlanMappingProvider
     */
    public function test_retail_plan_maps_to_gateway_plan_id(string $heroCode, string $interval, string $expectedGatewayId): void
    {
        $service = app(UsaPaymentsMembershipCheckoutService::class);
        $plan = new Plan([
            'code' => $heroCode,
            'billing_interval' => $interval === 'onetime' ? 'one_time' : ($interval === 'monthly' ? 'monthly' : 'yearly'),
            'price' => 100,
            'currency' => 'USD',
        ]);

        $this->assertSame($expectedGatewayId, $service->usaPaymentsPlanId($plan, $interval));
    }

    public static function retailPlanMappingProvider(): array
    {
        return [
            '10-day local' => ['HR-01A', 'onetime', 'HR-01A'],
            '10-day VIP' => ['HR-01AC', 'onetime', 'HR-01AC'],
            '1-month local' => ['HR-01B', 'onetime', 'HR-01B'],
            '1-month VIP' => ['HR-01BC', 'onetime', 'HR-01C'],
            'annual local yearly' => ['HR-02', 'yearly', 'HR-02Y'],
            'annual local monthly' => ['HR-02', 'monthly', 'HR-02M'],
            'annual VIP yearly' => ['HR-02C', 'yearly', 'HR-02CY'],
            'annual VIP monthly' => ['HR-02C', 'monthly', 'HR-02CM'],
            'family local yearly' => ['HR-03', 'yearly', 'HR-03-Y'],
            'family local monthly' => ['HR-03', 'monthly', 'HR-03-M'],
            'family VIP yearly' => ['HR-03C', 'yearly', 'HR-03CY'],
            'family VIP monthly' => ['HR-03C', 'monthly', 'HR-03CM-5'],
        ];
    }

    public function test_gateway_catalog_lists_all_twenty_aws_plans(): void
    {
        $catalog = config('usa_payments.gateway_plans');

        $this->assertCount(20, $catalog);
        $this->assertArrayHasKey('HR-03CM-5', $catalog);
        $this->assertArrayHasKey('HR-03CY-6', $catalog);
    }
}
