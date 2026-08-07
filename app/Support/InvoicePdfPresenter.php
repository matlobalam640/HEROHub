<?php

namespace App\Support;

use App\Models\Membership;

class InvoicePdfPresenter
{
    /**
     * @return array{
     *     logoBase64: string,
     *     companyName: string,
     *     website: string,
     *     websiteLabel: string,
     *     portalName: string
     * }
     */
    public static function brand(): array
    {
        $logoPath = public_path('brand/hero-logo.png');
        $logoBase64 = (self::supportsEmbeddedImages() && is_readable($logoPath))
            ? base64_encode((string) file_get_contents($logoPath))
            : '';

        $website = (string) config('heroportal.membership_subscribe_url', 'https://www.heroclientrescue.com/');

        return [
            'logoBase64' => $logoBase64,
            'companyName' => (string) config('heroportal.membership_card.company_name', 'HERO Client Rescue S.A.'),
            'website' => $website,
            'websiteLabel' => parse_url($website, PHP_URL_HOST) ?: 'www.heroclientrescue.com',
            'portalName' => (string) config('app.name', 'HERO Membership Portal'),
        ];
    }

    public static function memberName(Membership $membership): string
    {
        $membership->loadMissing(['members', 'accountUser']);

        $primary = $membership->members->firstWhere('is_primary', true)
            ?? $membership->members->first();

        if ($primary) {
            return trim($primary->first_name.' '.$primary->last_name);
        }

        return (string) ($membership->accountUser?->name ?? 'Member');
    }

    protected static function supportsEmbeddedImages(): bool
    {
        return extension_loaded('gd') && function_exists('imagecreatetruecolor');
    }
}
