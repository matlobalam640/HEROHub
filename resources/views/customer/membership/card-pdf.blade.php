<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Membership card — {{ $card['membershipId'] ?? '' }}</title>
    <style>
        @page { margin: 28px; }
        * { box-sizing: border-box; }
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #0f172a;
            font-size: 11px;
            margin: 0;
        }
        .card {
            background: #e4e4e8;
            border-radius: 14px;
            padding: 22px 24px 26px;
            max-width: 420px;
            margin: 0 auto;
            border: 1px solid rgba(15, 23, 42, 0.08);
        }
        .row-top { width: 100%; margin-bottom: 18px; }
        .row-top td { vertical-align: top; }
        .logo { height: 42px; width: auto; }
        .company {
            font-size: 13px;
            font-weight: bold;
            padding-left: 10px;
            line-height: 1.25;
        }
        .status-label {
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #334155;
        }
        .status-value { font-size: 11px; font-weight: bold; margin-top: 2px; }
        .label {
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #334155;
            margin-top: 14px;
        }
        .member-name {
            font-size: 22px;
            font-weight: bold;
            margin-top: 4px;
            line-height: 1.15;
        }
        .two-col { width: 100%; margin-top: 6px; }
        .two-col td { vertical-align: top; width: 50%; }
        .type-text { font-size: 10px; font-weight: bold; line-height: 1.35; padding-right: 8px; }
        .id-text { font-size: 14px; font-weight: bold; font-family: DejaVu Sans Mono, monospace; text-align: right; }
        .exp-text { font-size: 12px; font-weight: bold; margin-top: 4px; }
        .qr-wrap { text-align: center; margin-top: 18px; }
        .qr-inner {
            display: inline-block;
            background: #fff;
            padding: 8px;
            border-radius: 8px;
            border: 1px solid #cbd5e1;
        }
        .qr-inner img { width: 180px; height: 180px; display: block; }
    </style>
</head>
<body>
    <div class="card">
        <table class="row-top" cellspacing="0" cellpadding="0">
            <tr>
                <td style="width: 48px;">
                    @if(!empty($card['logoBase64']))
                        <img class="logo" src="data:image/png;base64,{{ $card['logoBase64'] }}" alt="">
                    @else
                        <div class="logo-fallback" style="font-size: 11px; font-weight: bold; color: #00838f; line-height: 1.1;">HERO</div>
                    @endif
                </td>
                <td class="company">{{ $card['companyName'] ?? '' }}</td>
                <td style="text-align: right; width: 72px;">
                    <div class="status-label">Status</div>
                    <div class="status-value">{{ $card['status'] ?? '' }}</div>
                </td>
            </tr>
        </table>

        <div class="label">Member's name</div>
        <div class="member-name">{{ $card['memberName'] ?? '—' }}</div>

        <table class="two-col" cellspacing="0" cellpadding="0">
            <tr>
                <td>
                    <div class="label" style="margin-top: 12px;">Membership type</div>
                    <div class="type-text">{{ $card['membershipType'] ?? '—' }}</div>
                </td>
                <td style="text-align: right;">
                    <div class="label" style="margin-top: 12px;">Membership ID</div>
                    <div class="id-text">{{ $card['membershipId'] ?? '—' }}</div>
                </td>
            </tr>
        </table>

        <div class="label">Expiration</div>
        <div class="exp-text">{{ $card['expiration'] ?? '—' }}</div>

        @if(!empty($card['qrDataUri']))
            <div class="qr-wrap">
                <div class="qr-inner">
                    <img src="{{ $card['qrDataUri'] }}" alt="QR">
                </div>
            </div>
        @endif
    </div>
</body>
</html>
