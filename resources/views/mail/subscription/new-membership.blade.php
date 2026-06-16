@extends('mail.layouts.hero-branded')

@section('title', 'Your membership is ready')

@section('eyebrow', 'Your membership is ready')

@section('content')
    @php
        $loginUrl = route('login');
        $membershipUrl = route('customer.membership');
        $primary = '#283b69';
    @endphp

    <h1 style="margin:0 0 12px;font-size:22px;line-height:1.3;font-weight:700;color:#0f172a;">
        Welcome{{ $user->name ? ', '.$user->name : '' }}
    </h1>
    <p style="margin:0 0 20px;color:#475569;font-size:15px;line-height:1.65;">
        Your HERO membership <strong style="color:#0f172a;">{{ $membership->membership_number }}</strong> is linked to this email address
        @if($planName)
            for <strong style="color:#0f172a;">{{ $planName }}</strong>
        @endif.
    </p>

    @if($needsPasswordSetup && $passwordResetUrl)
        <p style="margin:0 0 20px;color:#475569;font-size:15px;line-height:1.65;">
            We created a portal account for you from your subscription. Set your password first, then you can sign in anytime.
        </p>
        <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin:0 0 16px;">
            <tr>
                <td style="border-radius:10px;background-color:{{ $primary }};">
                    <a href="{{ $passwordResetUrl }}" target="_blank" rel="noopener noreferrer" style="display:inline-block;padding:14px 28px;font-size:15px;font-weight:600;color:#ffffff;text-decoration:none;border-radius:10px;">
                        Create your portal password
                    </a>
                </td>
            </tr>
        </table>
    @elseif($needsPasswordSetup)
        <p style="margin:0 0 20px;padding:14px 16px;background-color:#fef3c7;border-radius:10px;color:#92400e;font-size:14px;line-height:1.55;">
            We could not attach a one-time password link (it may have been requested recently). On the sign-in page, use <strong>Forgot password</strong> with this email to set your password.
        </p>
    @else
        <p style="margin:0 0 20px;color:#475569;font-size:15px;line-height:1.65;">
            Sign in with this email and the password you already use for the portal.
        </p>
    @endif

    <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin:8px 0 28px;">
        <tr>
            <td style="border-radius:10px;background-color:{{ $primary }};">
                <a href="{{ $loginUrl }}" target="_blank" rel="noopener noreferrer" style="display:inline-block;padding:14px 28px;font-size:15px;font-weight:600;color:#ffffff;text-decoration:none;border-radius:10px;">
                    Sign in to the portal
                </a>
            </td>
        </tr>
    </table>

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin:0 0 24px;border-top:1px solid #e2e8f0;">
        <tr><td style="height:24px;font-size:0;line-height:0;">&nbsp;</td></tr>
    </table>

    <h2 style="margin:0 0 14px;font-size:15px;font-weight:700;color:#0f172a;text-transform:uppercase;letter-spacing:0.06em;">
        What to do next
    </h2>
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin:0 0 8px;">
        <tr>
            <td style="vertical-align:top;width:28px;padding:4px 0 0;font-size:14px;font-weight:700;color:{{ $primary }};">1.</td>
            <td style="padding:0 0 14px;color:#475569;font-size:14px;line-height:1.6;">
                <strong style="color:#0f172a;">My membership</strong> — coverage dates, plan, and details.
                <a href="{{ $membershipUrl }}" style="color:{{ $primary }};font-weight:600;text-decoration:underline;" target="_blank" rel="noopener noreferrer">Open My membership</a>
            </td>
        </tr>
        <tr>
            <td style="vertical-align:top;width:28px;padding:4px 0 0;font-size:14px;font-weight:700;color:{{ $primary }};">2.</td>
            <td style="padding:0 0 14px;color:#475569;font-size:14px;line-height:1.6;">
                <strong style="color:#0f172a;">Digital card</strong> — after sign-in, download your <strong>PDF membership card</strong> (QR for verification).
            </td>
        </tr>
        <tr>
            <td style="vertical-align:top;width:28px;padding:4px 0 0;font-size:14px;font-weight:700;color:{{ $primary }};">3.</td>
            <td style="padding:0 0 14px;color:#475569;font-size:14px;line-height:1.6;">
                <strong style="color:#0f172a;">Household</strong> — if your plan includes family coverage, add or update dependents from the same page when available.
            </td>
        </tr>
        <tr>
            <td style="vertical-align:top;width:28px;padding:4px 0 0;font-size:14px;font-weight:700;color:{{ $primary }};">4.</td>
            <td style="padding:0 0 0;color:#475569;font-size:14px;line-height:1.6;">
                <strong style="color:#0f172a;">Billing</strong> — charges, invoices, and payment method stay in <strong>Zoho Billing</strong>; this portal focuses on membership and your card.
            </td>
        </tr>
    </table>

    <p style="margin:24px 0 0;color:#64748b;font-size:13px;line-height:1.55;">
        Thanks for being with HERO,<br>
        <span style="color:#0f172a;font-weight:600;">{{ config('app.name') }}</span>
    </p>
@endsection
