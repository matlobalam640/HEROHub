<?php

namespace App\Services\HeroPortal;

class HeroPortalPlanMapper
{
    /**
     * @var array<string, string>
     */
    private const GATEWAY_TO_PORTAL = [
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
    ];

    public static function toPortalPlanCode(string $gatewayPlanId): string
    {
        return self::GATEWAY_TO_PORTAL[$gatewayPlanId] ?? $gatewayPlanId;
    }
}
