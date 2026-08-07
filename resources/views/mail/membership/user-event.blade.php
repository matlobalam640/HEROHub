@extends('mail.layouts.hero-branded')

@section('title', $subject ?? 'Membership update')
@section('eyebrow', $eyebrow ?? 'Membership notification')

@section('content')
    @php
        $primary = '#141d35';
        $memberName = $user?->name ?: 'Member';
    @endphp

    <h1 style="margin:0 0 12px;font-size:22px;line-height:1.3;font-weight:700;color:#0f172a;">
        Hello {{ $memberName }},
    </h1>

    <p style="margin:0 0 16px;color:#475569;font-size:15px;line-height:1.65;">
        {{ $headline ?? 'There is an update on your HERO membership.' }}
    </p>

    @if(!empty($membershipNumber))
        <p style="margin:0 0 16px;color:#475569;font-size:14px;line-height:1.65;">
            <strong style="color:#0f172a;">Membership #:</strong> {{ $membershipNumber }}
        </p>
    @endif

    @if(!empty($planName))
        <p style="margin:0 0 18px;color:#475569;font-size:14px;line-height:1.65;">
            <strong style="color:#0f172a;">Plan:</strong> {{ $planName }}
        </p>
    @endif

    @if(!empty($detailLines) && is_array($detailLines))
        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin:0 0 20px;border:1px solid #e2e8f0;border-radius:10px;">
            <tr>
                <td style="padding:14px 16px;background:#f8fafc;">
                    @foreach($detailLines as $line)
                        <p style="margin:0 0 10px;color:#334155;font-size:14px;line-height:1.6;">
                            {{ $line }}
                        </p>
                    @endforeach
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
        {{ $footerNote ?? 'If this looks incorrect, please contact HERO support.' }}
    </p>
@endsection
