{{-- Admin-only. Inline styles so gold gradient shows even if Tailwind build misses utilities. --}}
@props([
    'from' => 'retail',
])
@if(auth()->user()->hasRole('admin'))
    <a
        href="{{ route('admin.plans.create', ['from' => $from]) }}"
        class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold no-underline transition-opacity hover:opacity-92"
        style="background-image: var(--gradient-gold); border: 1px solid rgba(120, 53, 15, 0.22); color: #283b69; box-shadow: var(--hero-gold-shadow);"
    >
        <i class="fa-solid fa-plus text-xs opacity-95" style="color: inherit;" aria-hidden="true"></i>
        <span style="color: inherit;">Add plan</span>
    </a>
@endif
