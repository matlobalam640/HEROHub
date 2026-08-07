<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $invoice['invoice'] }}</title>
    <style>
        @page { margin: 0; }
        * { box-sizing: border-box; }
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #0f172a;
            font-size: 11px;
            line-height: 1.45;
            margin: 0;
            background: #f7f8fb;
        }
        .page {
            padding: 0 0 28px;
        }
        .brand-header {
            background: #141d35;
            color: #ffffff;
            padding: 24px 32px 22px;
        }
        .brand-header-table {
            width: 100%;
            border-collapse: collapse;
        }
        .brand-header-table td {
            vertical-align: middle;
        }
        .brand-logo {
            height: 38px;
            width: auto;
            display: block;
        }
        .brand-logo-fallback {
            font-size: 18px;
            font-weight: 700;
            letter-spacing: 0.04em;
            color: #ffffff;
        }
        .brand-company {
            padding-left: 12px;
            font-size: 13px;
            font-weight: 700;
            line-height: 1.3;
        }
        .brand-company-sub {
            display: block;
            margin-top: 3px;
            font-size: 10px;
            font-weight: 400;
            color: rgba(255, 255, 255, 0.78);
        }
        .invoice-badge {
            text-align: right;
        }
        .invoice-badge-label {
            font-size: 9px;
            font-weight: 700;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.72);
        }
        .invoice-badge-value {
            margin-top: 4px;
            font-size: 16px;
            font-weight: 700;
            color: #ffffff;
        }
        .content {
            padding: 24px 32px 0;
        }
        .intro {
            margin: 0 0 16px;
            color: #475569;
            font-size: 11px;
        }
        .meta-grid {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            border: 1px solid #dbe3ef;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 16px;
        }
        .meta-grid td {
            padding: 10px 14px;
            border-bottom: 1px solid #e8edf4;
            vertical-align: top;
        }
        .meta-grid tr:last-child td {
            border-bottom: 0;
        }
        .meta-label {
            width: 34%;
            background: #f8fafc;
            font-weight: 700;
            color: #334155;
        }
        .meta-value {
            color: #0f172a;
        }
        .line-items {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #dbe3ef;
            border-radius: 10px;
            overflow: hidden;
        }
        .line-items th {
            background: #141d35;
            color: #ffffff;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            text-align: left;
            padding: 10px 14px;
        }
        .line-items th.amount-col {
            text-align: right;
            width: 28%;
        }
        .line-items td {
            padding: 12px 14px;
            border-top: 1px solid #e8edf4;
            vertical-align: top;
        }
        .line-items td.amount-col {
            text-align: right;
            font-size: 15px;
            font-weight: 700;
            color: #141d35;
        }
        .status-paid {
            display: inline-block;
            background: rgba(62, 207, 202, 0.16);
            color: #1e6f6c;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            padding: 4px 8px;
            border-radius: 999px;
        }
        .footer {
            margin-top: 22px;
            padding-top: 14px;
            border-top: 1px solid #dbe3ef;
            color: #64748b;
            font-size: 9px;
            line-height: 1.5;
        }
        .footer strong {
            color: #334155;
        }
        .accent-bar {
            height: 4px;
            background: #3ecfca;
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="accent-bar"></div>

        <div class="brand-header">
            <table class="brand-header-table" cellspacing="0" cellpadding="0">
                <tr>
                    <td style="width: 52px;">
                        @if(! empty($brand['logoBase64']))
                            <img class="brand-logo" src="data:image/png;base64,{{ $brand['logoBase64'] }}" alt="">
                        @else
                            <div class="brand-logo-fallback">HERO</div>
                        @endif
                    </td>
                    <td class="brand-company">
                        {{ $brand['companyName'] }}
                        <span class="brand-company-sub">{{ $brand['portalName'] }}</span>
                    </td>
                    <td class="invoice-badge" style="width: 38%;">
                        <div class="invoice-badge-label">Invoice</div>
                        <div class="invoice-badge-value">{{ $invoice['invoice'] }}</div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="content">
            <p class="intro">
                Thank you for your HERO membership. This invoice confirms payment received for the billing period below.
            </p>

            <table class="meta-grid" cellspacing="0" cellpadding="0">
                <tr>
                    <td class="meta-label">Bill to</td>
                    <td class="meta-value">{{ $memberName }}</td>
                </tr>
                <tr>
                    <td class="meta-label">Membership #</td>
                    <td class="meta-value">{{ $membership->membership_number }}</td>
                </tr>
                <tr>
                    <td class="meta-label">Plan</td>
                    <td class="meta-value">{{ $membership->plan?->name ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="meta-label">Billing period</td>
                    <td class="meta-value">{{ $invoice['period'] }}</td>
                </tr>
                <tr>
                    <td class="meta-label">Paid on</td>
                    <td class="meta-value">{{ $invoice['paid_at'] }}</td>
                </tr>
            </table>

            <table class="line-items" cellspacing="0" cellpadding="0">
                <thead>
                    <tr>
                        <th>Description</th>
                        <th class="amount-col">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            Membership subscription for {{ $invoice['period'] }}
                            <br>
                            <span class="status-paid">{{ ucfirst($invoice['status']) }}</span>
                        </td>
                        <td class="amount-col">${{ $invoice['amount'] }} USD</td>
                    </tr>
                </tbody>
            </table>

            <div class="footer">
                <strong>{{ $brand['companyName'] }}</strong><br>
                {{ $brand['websiteLabel'] }} · {{ $brand['portalName'] }}<br>
                Questions about billing? Contact HERO support through your membership portal.
            </div>
        </div>
    </div>
</body>
</html>
