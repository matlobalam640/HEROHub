<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'HERO Membership Portal') }}</title>

        @include('layouts.partials.favicon')

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|montserrat:500,600,700,800|open-sans:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased text-slate-900">
        <div class="hero-guest-canvas flex min-h-screen flex-col">
            @include('layouts.partials.hero-site-brand-bar', ['portalLabel' => 'Member Sign In'])

            <div class="mx-auto grid w-full max-w-6xl flex-1 grid-cols-1 items-stretch gap-8 px-4 py-10 sm:px-6 lg:grid-cols-2 lg:gap-10 lg:py-14">
                <div class="hidden lg:flex lg:min-h-[620px]">
                    <div class="guest-auth-panel flex w-full flex-col overflow-hidden">
                        <div class="relative min-h-[360px] flex-1 overflow-hidden bg-[color:var(--hero-primary)]">
                            <img
                                src="{{ asset('images/banner-image.avif') }}"
                                alt="HERO emergency response team"
                                class="guest-auth-hero-photo absolute inset-0 h-full w-full object-cover object-center"
                                loading="eager"
                                decoding="async"
                                fetchpriority="high"
                            >
                            <div class="absolute inset-0 bg-gradient-to-t from-[#162339] via-[color:var(--hero-primary)]/60 to-[color:var(--hero-primary)]/20"></div>
                            <div class="absolute inset-x-0 bottom-0 p-10">
                                <p class="max-w-md font-display text-3xl font-bold leading-tight tracking-tight text-white sm:text-4xl">
                                    One Mission, One Team.
                                </p>
                                <p class="mt-4 max-w-md text-base leading-relaxed text-white/85">
                                    Secure member portal for coverage, billing, and emergency verification — built for HERO members worldwide.
                                </p>
                            </div>
                        </div>

                        <div class="grid shrink-0 gap-6 border-t border-slate-100/80 bg-white/80 p-8 backdrop-blur-sm">
                            <div class="flex items-start gap-4">
                                <span class="inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl text-lg shadow-sm" style="background: var(--gradient-gold-soft); color: var(--hero-gold-500);">
                                    <i class="fa-solid fa-user-shield" aria-hidden="true"></i>
                                </span>
                                <div>
                                    <div class="font-display text-base font-semibold text-[color:var(--hero-primary)]">Instant coverage verification</div>
                                    <p class="mt-1 text-sm leading-relaxed text-slate-600">Dispatch confirms coverage by member name, ID, phone, or company in seconds.</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-4">
                                <span class="inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl text-lg shadow-sm" style="background: var(--gradient-gold-soft); color: var(--hero-gold-500);">
                                    <i class="fa-solid fa-lock" aria-hidden="true"></i>
                                </span>
                                <div>
                                    <div class="font-display text-base font-semibold text-[color:var(--hero-primary)]">Secure self-service</div>
                                    <p class="mt-1 text-sm leading-relaxed text-slate-600">Members, businesses, and partners manage active memberships in one place.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex min-h-[560px] flex-col lg:min-h-0">
                    <div class="guest-auth-panel flex flex-1 flex-col justify-center p-8 sm:p-10 animate-fade-up">
                        {{ $slot }}
                    </div>
                </div>
            </div>

            <footer class="hero-auth-footer">
                © {{ now()->year }} HERO Client Rescue S.A. · Bridging critical medical &amp; emergency infrastructure gaps in Haiti
            </footer>
        </div>
    </body>
</html>
