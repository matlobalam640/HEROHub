@php
    $membershipPortalEyebrow = auth()->user()->hasRole('business') && ! auth()->user()->hasRole('customer')
        ? 'Company member'
        : 'Customer';
@endphp
<div class="text-sm font-medium text-hero-primary">{{ $membershipPortalEyebrow }}</div>
