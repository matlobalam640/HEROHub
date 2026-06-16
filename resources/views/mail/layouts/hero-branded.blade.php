{{-- Table layout + inline styles for broad email client support. --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>@yield('title', config('app.name'))</title>
</head>
<body style="margin:0;padding:0;background-color:#f1f5f9;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%;font-family:Segoe UI,Roboto,Helvetica,Arial,sans-serif;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#f1f5f9;">
        <tr>
            <td align="center" style="padding:24px 16px 40px;">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width:600px;margin:0 auto;">
                    <tr>
                        <td style="background:linear-gradient(135deg,#283b69 0%,#1f2d52 100%);background-color:#283b69;border-radius:16px 16px 0 0;padding:28px 32px 24px;text-align:left;">
                            <a href="{{ url('/') }}" style="text-decoration:none;display:inline-block;" target="_blank" rel="noopener noreferrer">
                                <img src="{{ asset('brand/hero-logo.png') }}" alt="{{ config('app.name') }}" width="160" height="auto" style="display:block;max-width:160px;height:auto;border:0;outline:none;">
                            </a>
                            <p style="margin:16px 0 0;font-size:13px;line-height:1.5;color:rgba(255,255,255,0.88);font-weight:600;letter-spacing:0.02em;">
                                @yield('eyebrow', 'Membership portal')
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color:#ffffff;padding:0 1px;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                <tr>
                                    <td style="padding:32px 32px 28px;color:#0f172a;font-size:16px;line-height:1.65;">
                                        @yield('content')
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color:#e2e8f0;border-radius:0 0 16px 16px;padding:20px 32px;text-align:center;">
                            <p style="margin:0 0 8px;font-size:12px;line-height:1.5;color:#64748b;">
                                {{ config('app.name') }} · {{ parse_url(config('app.url'), PHP_URL_HOST) ?: 'portal' }}
                            </p>
                            <p style="margin:0;font-size:11px;line-height:1.5;color:#94a3b8;">
                                You received this because a subscription was linked to your account.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
