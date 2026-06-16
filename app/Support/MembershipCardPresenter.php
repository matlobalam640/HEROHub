<?php

namespace App\Support;

use App\Models\Membership;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\SvgWriter;

class MembershipCardPresenter
{
    public static function from(Membership $membership): array
    {
        $membership->loadMissing(['plan', 'members']);

        $primary = $membership->members->firstWhere('is_primary', true)
            ?? $membership->members->first();

        $plan = $membership->plan;
        $membershipType = $plan
            ? trim($plan->code.' - '.$plan->name)
            : '—';

        $payload = self::qrPayload($membership, $primary);

        $qrCode = new QrCode(
            data: $payload,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::Medium,
            size: 280,
            margin: 8,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
        );

        // PNG needs PHP GD; SVG works on minimal XAMPP / CLI PHP without GD.
        $qrResult = self::qrWriterSupportsGd()
            ? (new PngWriter)->write($qrCode)
            : (new SvgWriter)->write($qrCode);

        // Dompdf embeds PNG via GD (imagecreatefrompng). Without GD, skip logo in PDF — QR can stay SVG.
        $logoPath = public_path('brand/hero-logo.png');
        $logoBase64 = (self::qrWriterSupportsGd() && is_readable($logoPath))
            ? base64_encode((string) file_get_contents($logoPath))
            : '';

        return [
            'companyName' => (string) config('heroportal.membership_card.company_name'),
            'memberName' => $primary ? trim($primary->first_name.' '.$primary->last_name) : '—',
            'membershipType' => $membershipType,
            'membershipId' => (string) $membership->membership_number,
            'expiration' => $membership->coverage_ends_on?->format('F j, Y') ?? '—',
            'status' => ucfirst((string) $membership->status),
            'isActive' => $membership->status === 'active',
            'qrDataUri' => $qrResult->getDataUri(),
            'logoBase64' => $logoBase64,
        ];
    }

    protected static function qrPayload(Membership $membership, $primary): string
    {
        return json_encode([
            'v' => 1,
            'membership' => $membership->membership_number,
            'token' => $primary?->qr_token ?? '',
        ], JSON_THROW_ON_ERROR);
    }

    protected static function qrWriterSupportsGd(): bool
    {
        return extension_loaded('gd') && function_exists('imagecreatetruecolor');
    }
}
