<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #0f172a; font-size: 12px; }
        .container { max-width: 760px; margin: 0 auto; }
        .header { margin-bottom: 20px; }
        .title { font-size: 22px; font-weight: 700; margin: 0 0 4px; }
        .muted { color: #475569; }
        .box { border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; margin-top: 10px; }
        .row { width: 100%; border-collapse: collapse; margin-top: 14px; }
        .row td { padding: 8px 10px; border: 1px solid #e2e8f0; }
        .label { width: 35%; background: #f8fafc; font-weight: 700; }
        .amount { font-size: 16px; font-weight: 700; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <p class="title">HERO Invoice</p>
            <p class="muted">HERO Client Rescue S.A.</p>
        </div>

        <div class="box">
            <p><strong>Invoice #:</strong> {{ $invoice['invoice'] }}</p>
            <p><strong>Membership #:</strong> {{ $membership->membership_number }}</p>
            <p><strong>Plan:</strong> {{ $membership->plan?->name ?? 'N/A' }}</p>
            <p><strong>Billing Period:</strong> {{ $invoice['period'] }}</p>
            <p><strong>Paid At:</strong> {{ $invoice['paid_at'] }}</p>
        </div>

        <table class="row">
            <tr>
                <td class="label">Description</td>
                <td>Membership subscription for {{ $invoice['period'] }}</td>
            </tr>
            <tr>
                <td class="label">Status</td>
                <td>{{ ucfirst($invoice['status']) }}</td>
            </tr>
            <tr>
                <td class="label">Amount</td>
                <td class="amount">${{ $invoice['amount'] }}</td>
            </tr>
        </table>
    </div>
</body>
</html>
