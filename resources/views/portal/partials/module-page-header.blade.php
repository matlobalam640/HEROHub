@php
    $eyebrow = $eyebrow ?? 'Module';
    $metrics = $metrics ?? [];
@endphp
<div class="hero-portal-page-header rounded-2xl border border-slate-200/80 bg-gradient-to-r from-white via-white to-slate-50/95 px-4 py-3 shadow-sm ring-1 ring-slate-100 sm:px-5 sm:py-3.5">
    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <div class="min-w-0 lg:max-w-[55%]">
            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">{{ $eyebrow }}</div>
            <h1 class="mt-0.5 truncate font-display text-xl font-bold tracking-tight text-slate-900 sm:text-2xl dark:text-slate-100 {{ ($capitalizeTitle ?? true) ? 'capitalize' : '' }} {{ $titleClass ?? '' }}">
                {{ $title }}
            </h1>
        </div>
        @if(count($metrics) || ! empty($toolbarLink))
            <div class="flex max-w-full flex-wrap items-stretch gap-2 lg:max-w-[50%] lg:justify-end">
                @foreach($metrics as $metric)
                    <div class="hero-portal-page-header__metric flex min-w-[5.5rem] flex-1 flex-col rounded-xl border border-slate-200/90 bg-white/95 px-3 py-2 shadow-sm sm:flex-initial sm:min-w-[6.75rem]">
                        <span class="text-[10px] font-semibold uppercase leading-tight tracking-wide text-slate-500 dark:text-slate-400">{{ $metric['label'] }}</span>
                        <span class="mt-0.5 text-base font-bold tabular-nums leading-none text-slate-900 sm:text-lg dark:text-slate-100">
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
                        class="hero-portal-page-header__action inline-flex flex-1 items-center justify-center gap-2 self-center rounded-xl border border-slate-200/90 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 sm:flex-initial"
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
