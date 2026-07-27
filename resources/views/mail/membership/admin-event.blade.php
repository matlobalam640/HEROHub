@extends('mail.layouts.hero-branded')

@section('title', $subject ?? 'Membership event (admin)')
@section('eyebrow', 'Admin membership alert')

@section('content')
    @php
        $primary = '#283b69';
    @endphp

    <h1 style="margin:0 0 12px;font-size:21px;line-height:1.3;font-weight:700;color:#0f172a;">
        {{ $headline ?? 'A membership event needs your attention.' }}
    </h1>

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
        <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin:6px 0 20px;">
            <tr>
                <td style="border-radius:10px;background-color:{{ $primary }};">
                    <a href="{{ $actionUrl }}" target="_blank" rel="noopener noreferrer" style="display:inline-block;padding:13px 22px;font-size:14px;font-weight:600;color:#ffffff;text-decoration:none;border-radius:10px;">
                        {{ $actionLabel }}
                    </a>
                </td>
            </tr>
        </table>
    @endif

    <p style="margin:16px 0 0;color:#64748b;font-size:13px;line-height:1.55;">
        This message was sent to admin-role users in the portal.
    </p>
@endsection
