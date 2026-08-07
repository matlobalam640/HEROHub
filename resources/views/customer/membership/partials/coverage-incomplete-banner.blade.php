@if(!empty($coverageProfileIncomplete) && ! request()->routeIs('customer.membership.coverage'))
    @php
        $missing = $coverageProfileMissingLabels ?? [];
        $visible = array_slice($missing, 0, 5);
        $remaining = max(count($missing) - count($visible), 0);
    @endphp
    <div class="hero-coverage-banner mb-5" role="status">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex min-w-0 items-start gap-3">
                <span class="hero-coverage-banner__icon" aria-hidden="true">
                    <i class="fa-solid fa-circle-exclamation"></i>
                </span>
                <div class="min-w-0">
                    <p class="hero-coverage-banner__title">{{ \App\Support\CoverageFormTranslations::t('banner_complete') }}</p>
                    @if ($missing !== [])
                        <div class="mt-2 flex flex-wrap gap-1.5">
                            @foreach ($visible as $label)
                                <span class="hero-coverage-banner__tag">{{ $label }}</span>
                            @endforeach
                            @if ($remaining > 0)
                                <span class="hero-coverage-banner__tag hero-coverage-banner__tag--more">+{{ $remaining }} more</span>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
            <a href="{{ route('customer.membership.coverage') }}" class="hero-coverage-banner__cta">
                {{ \App\Support\CoverageFormTranslations::t('banner_complete_now') }}
            </a>
        </div>
    </div>
@endif
