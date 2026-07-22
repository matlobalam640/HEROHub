@props([
    'plan',
    'from' => 'retail',
])
@if(auth()->user()->hasRole('admin'))
    <a
        href="{{ route('admin.plans.edit', ['plan' => $plan, 'from' => $from]) }}"
        class="inline-flex items-center gap-2 rounded-lg border border-hero-primary/35 bg-white px-3 py-1.5 text-xs font-semibold text-hero-primary transition hover:border-hero-primary hover:bg-hero-primary hover:text-white"
    >
        <i class="fa-solid fa-pen-to-square text-[11px]" aria-hidden="true"></i>
        <span>Edit plan</span>
    </a>
@endif
