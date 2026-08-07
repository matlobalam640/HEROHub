@php
    $href = $href ?? null;
    $trend = $trend ?? null;
    $tag = $href ? 'a' : 'div';
@endphp
<{{ $tag }}
    @if($href) href="{{ $href }}" @endif
    class="dashboard-stat-card group block h-full no-underline {{ $href ? 'cursor-pointer' : '' }}"
>
    <div class="flex items-start justify-between gap-3">
        <div class="dashboard-stat-icon--{{ $iconVariant ?? 'vuexy-primary' }} flex h-10 w-10 shrink-0 items-center justify-center rounded-xl text-sm">
            <i class="{{ $icon }}" aria-hidden="true"></i>
        </div>
        @if($trend)
            <span class="dashboard-stat-card__trend">
                {{ $trend }}
            </span>
        @endif
    </div>
    <div class="dashboard-stat-card__label mt-3">{{ $label }}</div>
    <div class="dashboard-stat-card__value">{{ $value }}</div>
    @if(! empty($hint))
        <div class="dashboard-stat-card__hint">{!! $hint !!}</div>
    @else
        <div class="dashboard-stat-card__hint" aria-hidden="true">&nbsp;</div>
    @endif
    @if($href)
        <div class="dashboard-stat-card__link">
            View details
            <i class="fa-solid fa-arrow-right text-[10px]" aria-hidden="true"></i>
        </div>
    @endif
</{{ $tag }}>
