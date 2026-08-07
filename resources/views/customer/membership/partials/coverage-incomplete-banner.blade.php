@if(!empty($coverageProfileIncomplete) && ! request()->routeIs('customer.membership.coverage'))
    @php
        $missing = $coverageProfileMissingLabels ?? [];
        $visible = array_slice($missing, 0, 5);
        $remaining = max(count($missing) - count($visible), 0);
    @endphp
    <div class="mb-5 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-950 shadow-sm sm:px-5" role="status">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex min-w-0 items-start gap-3">
                <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-amber-100 text-amber-700">
                    <i class="fa-solid fa-bell text-sm" aria-hidden="true"></i>
                </span>
                <div class="min-w-0">
                    <p class="font-semibold text-amber-950">{{ \App\Support\CoverageFormTranslations::en('banner_complete') }}</p>
                    @if ($missing !== [])
                        <div class="mt-2 flex flex-wrap gap-1.5">
                            @foreach ($visible as $label)
                                <span class="inline-flex rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-900">{{ $label }}</span>
                            @endforeach
                            @if ($remaining > 0)
                                <span class="inline-flex rounded-full bg-amber-100/70 px-2.5 py-0.5 text-xs text-amber-900">+{{ $remaining }} more</span>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
            <a href="{{ route('customer.membership.coverage') }}"
               class="inline-flex shrink-0 items-center justify-center rounded-lg bg-hero-primary px-4 py-2 text-sm font-semibold text-white transition hover:bg-hero-primary-hover">
                {{ \App\Support\CoverageFormTranslations::en('banner_complete_now') }}
            </a>
        </div>
    </div>
@endif
