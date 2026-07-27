@extends('mail.layouts.hero-branded')

@section('title', $subject ?? 'Membership renewal reminder')
@section('eyebrow', 'Renewal reminder')

@section('content')
    @php
        $primary = '#283b69';
        $daysLabel = isset($daysUntilRenewal) ? (int) $daysUntilRenewal : null;
    @endphp

    <h1 style="margin:0 0 12px;font-size:22px;line-height:1.3;font-weight:700;color:#0f172a;">
        Membership renewal reminder
    </h1>

    <p style="margin:0 0 16px;color:#475569;font-size:15px;line-height:1.65;">
        @if($daysLabel === 0)
            Your HERO membership renews today.
        @elseif($daysLabel === 1)
            Your HERO membership renews tomorrow.
        @elseif($daysLabel !== null)
            Your HERO membership renews in {{ $daysLabel }} days.
        @else
            Your HERO membership has an upcoming renewal.
        @endif
    </p>

    @if(!empty($membershipNumber) || !empty($planName) || !empty($renewalDate))
        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin:0 0 22px;border:1px solid #e2e8f0;border-radius:10px;">
            <tr>
                <td style="padding:14px 16px;background:#f8fafc;">
                    @if(!empty($membershipNumber))
                        <p style="margin:0 0 8px;color:#334155;font-size:14px;line-height:1.6;"><strong style="color:#0f172a;">Membership #:</strong> {{ $membershipNumber }}</p>
                    @endif
                    @if(!empty($planName))
                        <p style="margin:0 0 8px;color:#334155;font-size:14px;line-height:1.6;"><strong style="color:#0f172a;">Plan:</strong> {{ $planName }}</p>
                    @endif
                    @if(!empty($renewalDate))
                        <p style="margin:0;color:#334155;font-size:14px;line-height:1.6;"><strong style="color:#0f172a;">Renewal date:</strong> {{ $renewalDate }}</p>
                    @endif
                </td>
            </tr>
        </table>
    @endif

    @if(!empty($actionUrl) && !empty($actionLabel))
        <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin:8px 0 22px;">
            <tr>
                <td style="border-radius:10px;background-color:{{ $primary }};">
                    <a href="{{ $actionUrl }}" target="_blank" rel="noopener noreferrer" style="display:inline-block;padding:14px 24px;font-size:15px;font-weight:600;color:#ffffff;text-decoration:none;border-radius:10px;">
                        {{ $actionLabel }}
                    </a>
                </td>
            </tr>
        </table>
    @endif

    <p style="margin:20px 0 0;color:#64748b;font-size:13px;line-height:1.55;">
        {{ $footerNote ?? 'Please ensure your billing information is current to avoid coverage interruption.' }}
    </p>
@endsection
