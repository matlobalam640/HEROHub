@props([
    'plan',
    'from' => 'retail',
    'goldCard' => false,
])
@if(auth()->user()->hasRole('admin'))
    <a
        href="{{ route('admin.plans.edit', ['plan' => $plan, 'from' => $from]) }}"
        @class([
            'inline-flex items-center gap-2 rounded-lg px-3 py-1.5 text-xs font-semibold transition',
            'border border-hero-primary/35 bg-white text-hero-primary hover:border-hero-primary hover:bg-hero-primary hover:text-white' => $goldCard,
            'border border-amber-800/25 bg-gradient-to-b from-[#faf6d4] via-[#f2e088] to-[#e8d76a] text-hero-primary shadow-[0_2px_8px_-2px_rgba(212,168,83,0.35)] hover:border-hero-primary hover:from-[#f2e088] hover:to-[#dec328] hover:text-white' => ! $goldCard,
        ])
    >
        <i class="fa-solid fa-pen-to-square text-[11px]" aria-hidden="true"></i>
        <span>Edit plan</span>
    </a>
@endif
