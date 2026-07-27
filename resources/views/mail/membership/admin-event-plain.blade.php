{{ $subject ?? 'Membership event (admin)' }}
===========================================

{{ $headline ?? 'A membership event needs your attention.' }}

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

---
{{ config('app.name') }} · {{ config('app.url') }}
