@php
    $eyebrow = $eyebrow ?? 'Module';
    $metrics = $metrics ?? [];
@endphp
<div class="hero-portal-page-header mb-8">
    <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
        <div class="min-w-0 lg:max-w-[58%]">
            <div class="text-xs font-bold uppercase tracking-[0.2em] text-[color:var(--hero-accent-teal)]">{{ $eyebrow }}</div>
            <h1 class="mt-2 font-display text-3xl font-bold tracking-tight text-[color:var(--hero-primary)] sm:text-4xl lg:text-[2.75rem] lg:leading-[1.1] {{ ($capitalizeTitle ?? true) ? 'capitalize' : '' }} {{ $titleClass ?? '' }}">
                {{ $title }}
            </h1>
            @if(! empty($subtitle))
                <p class="mt-3 max-w-2xl text-base leading-relaxed text-slate-600">{{ $subtitle }}</p>
            @endif
        </div>
        @if(count($metrics) || ! empty($toolbarLink))
            <div class="flex max-w-full flex-wrap items-stretch gap-3 lg:max-w-[42%] lg:justify-end">
                @foreach($metrics as $metric)
                    <div class="hero-portal-page-header__metric flex min-w-[6rem] flex-1 flex-col sm:flex-initial sm:min-w-[7.5rem]">
                        <span class="text-[10px] font-semibold uppercase leading-tight tracking-wide text-slate-500">{{ $metric['label'] }}</span>
                        <span class="mt-1 text-lg font-bold tabular-nums leading-none text-[color:var(--hero-primary)]">
                            @if(is_int($metric['value']) || is_float($metric['value']))
                                {{ number_format($metric['value']) }}
                            @else
                                {{ $metric['value'] }}
                            @endif
                        </span>
                    </div>
                @endforeach
                @if(! empty($toolbarLink))
                    <a href="{{ $toolbarLink['href'] }}" class="hero-btn-secondary inline-flex flex-1 items-center justify-center gap-2 self-center sm:flex-initial">
                        @if(! empty($toolbarLink['icon']))
                            <i class="{{ $toolbarLink['icon'] }} text-xs opacity-80" aria-hidden="true"></i>
                        @endif
                        {{ $toolbarLink['label'] }}
                    </a>
                @endif
            </div>
        @endif
    </div>
</div>
