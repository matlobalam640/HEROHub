@php
    $homeUrl = $homeUrl ?? config('heroportal.membership_subscribe_url', 'https://www.heroclientrescue.com/');
    $portalLabel = $portalLabel ?? 'Membership Portal';
    $showWebsiteLink = $showWebsiteLink ?? true;
@endphp
<header class="hero-site-brand-bar">
    <div class="hero-site-brand-bar__inner">
        <a href="{{ $homeUrl }}" class="hero-site-brand-bar__logo" @if(str_starts_with($homeUrl, 'http')) target="_blank" rel="noopener noreferrer" @endif>
            <img
                src="{{ asset('brand/hero-logo.png') }}"
                alt="HERO Client Rescue"
                class="h-9 w-auto max-w-[9.5rem] object-contain brightness-110"
                width="152"
                height="36"
                loading="eager"
                decoding="async"
            >
            <span class="hero-site-brand-bar__title">{{ $portalLabel }}</span>
        </a>

        @if($showWebsiteLink)
            <nav class="hero-site-brand-bar__nav" aria-label="External links">
                <a href="https://www.heroclientrescue.com/" target="_blank" rel="noopener noreferrer">
                    Main website
                </a>
                <a href="https://www.heroclientrescue.com/" target="_blank" rel="noopener noreferrer">
                    Become a member
                </a>
            </nav>
        @endif
    </div>
</header>
