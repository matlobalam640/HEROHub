{{ $subject ?? 'Membership update' }}
================================

Hello {{ $user?->name ?: 'Member' }},

{{ $headline ?? 'There is an update on your HERO membership.' }}

@if(!empty($membershipNumber))
Membership #: {{ $membershipNumber }}
@endif
@if(!empty($planName))
Plan: {{ $planName }}
@endif

@if(!empty($detailLines) && is_array($detailLines))
Details
-------
@foreach($detailLines as $line)
- {{ $line }}
@endforeach
@endif

@if(!empty($actionUrl) && !empty($actionLabel))
{{ $actionLabel }}: {{ $actionUrl }}
@endif

{{ $footerNote ?? 'If this looks incorrect, please contact HERO support.' }}

---
{{ config('app.name') }} · {{ config('app.url') }}
