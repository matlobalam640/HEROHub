{{-- Admin-only. Plain CSS classes so gold/white buttons render on production builds. --}}
@props([
    'plan',
    'from' => 'retail',
    'goldCard' => false,
])
@if(auth()->user()->hasRole('admin'))
    <a
        href="{{ route('admin.plans.edit', ['plan' => $plan, 'from' => $from]) }}"
        class="plan-edit-btn {{ $goldCard ? 'plan-edit-btn--on-gold-card' : 'plan-edit-btn--on-white-card' }}"
    >
        <i class="fa-solid fa-pen-to-square text-[11px]" aria-hidden="true"></i>
        <span>Edit plan</span>
    </a>
@endif
