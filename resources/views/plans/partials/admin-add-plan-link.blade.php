{{-- Admin-only. Inline styles so navy + white show even if Tailwind arbitrary colors are missing from build. --}}
@props([
    'from' => 'retail',
])
@if(auth()->user()->hasRole('admin'))
    <a
        href="{{ route('admin.plans.create', ['from' => $from]) }}"
        class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold no-underline transition-opacity hover:opacity-92"
        style="background-color: #283b69; border: 2px solid #283b69; color: #ffffff; box-shadow: 0 6px 18px -4px rgba(40, 59, 105, 0.45);"
    >
        <i class="fa-solid fa-plus text-xs opacity-95" style="color: inherit;" aria-hidden="true"></i>
        <span style="color: #ffffff;">Add plan</span>
    </a>
@endif
