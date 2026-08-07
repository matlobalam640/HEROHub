@php
    $href = $href ?? null;
    $trend = $trend ?? null;
    $tag = $href ? 'a' : 'div';
@endphp
<{{ $tag }}
    @if($href) href="{{ $href }}" @endif
    class="dashboard-stat-card group block no-underline {{ $href ? 'cursor-pointer' : '' }}"
>
    <div class="flex items-start justify-between gap-3">
        <div class="dashboard-stat-icon--{{ $iconVariant ?? 'vuexy-primary' }} flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl text-lg">
            <i class="{{ $icon }}" aria-hidden="true"></i>
        </div>
        @if($trend)
            <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-semibold text-emerald-700 ring-1 ring-emerald-100">
                {{ $trend }}
            </span>
        @endif
    </div>
    <div class="mt-4 text-sm font-medium text-slate-500">{{ $label }}</div>
    <div class="mt-1 font-display text-3xl font-bold tracking-tight text-slate-900">{{ $value }}</div>
    @if(! empty($hint))
        <div class="mt-2 text-xs leading-relaxed text-slate-500">{!! $hint !!}</div>
    @endif
    @if($href)
        <div class="mt-3 inline-flex items-center gap-1 text-xs font-semibold text-[color:var(--vuexy-primary)] opacity-0 transition group-hover:opacity-100">
            View details
            <i class="fa-solid fa-arrow-right text-[10px]" aria-hidden="true"></i>
        </div>
    @endif
</{{ $tag }}>
