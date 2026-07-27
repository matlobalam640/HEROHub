{{ $subject ?? 'Membership renewal reminder' }}
==============================================

@if(isset($daysUntilRenewal) && (int) $daysUntilRenewal === 0)
Your HERO membership renews today.
@elseif(isset($daysUntilRenewal) && (int) $daysUntilRenewal === 1)
Your HERO membership renews tomorrow.
@elseif(isset($daysUntilRenewal))
Your HERO membership renews in {{ (int) $daysUntilRenewal }} days.
@else
Your HERO membership has an upcoming renewal.
@endif

@if(!empty($membershipNumber))
Membership #: {{ $membershipNumber }}
@endif
@if(!empty($planName))
Plan: {{ $planName }}
@endif
@if(!empty($renewalDate))
Renewal date: {{ $renewalDate }}
@endif

@if(!empty($actionUrl) && !empty($actionLabel))
{{ $actionLabel }}: {{ $actionUrl }}
@endif

{{ $footerNote ?? 'Please ensure your billing information is current to avoid coverage interruption.' }}

---
{{ config('app.name') }} · {{ config('app.url') }}
