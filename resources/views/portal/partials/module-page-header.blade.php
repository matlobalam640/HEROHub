@php
    $eyebrow = $eyebrow ?? 'Module';
    $metrics = $metrics ?? [];
@endphp
<div class="hero-portal-page-header">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div class="min-w-0 lg:max-w-[55%]">
            <div class="text-[11px] font-bold uppercase tracking-[0.18em] text-[color:var(--hero-primary)]">{{ $eyebrow }}</div>
            <h1 class="mt-1 truncate font-display text-xl font-bold tracking-tight text-[color:var(--hero-primary)] sm:text-2xl dark:text-slate-100 {{ ($capitalizeTitle ?? true) ? 'capitalize' : '' }} {{ $titleClass ?? '' }}">
                {{ $title }}
            </h1>
        </div>
        @if(count($metrics) || ! empty($toolbarLink))
            <div class="flex max-w-full flex-wrap items-stretch gap-2 lg:max-w-[50%] lg:justify-end">
                @foreach($metrics as $metric)
                    <div class="hero-portal-page-header__metric flex min-w-[5.5rem] flex-1 flex-col sm:flex-initial sm:min-w-[6.75rem]">
                        <span class="text-[10px] font-semibold uppercase leading-tight tracking-wide text-slate-500 dark:text-slate-400">{{ $metric['label'] }}</span>
                        <span class="mt-0.5 text-base font-bold tabular-nums leading-none text-[color:var(--hero-primary)] sm:text-lg dark:text-slate-100">
                            @if(is_int($metric['value']) || is_float($metric['value']))
                                {{ number_format($metric['value']) }}
                            @else
                                {{ $metric['value'] }}
                            @endif
                        </span>
                    </div>
                @endforeach
                @if(! empty($toolbarLink))
                    <a
                        href="{{ $toolbarLink['href'] }}"
                        class="hero-btn-secondary inline-flex flex-1 items-center justify-center gap-2 self-center sm:flex-initial"
                    >
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
