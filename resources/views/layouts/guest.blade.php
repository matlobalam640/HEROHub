<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        @include('layouts.partials.favicon')

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|montserrat:500,600,700|open-sans:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased text-slate-900">
        <div class="flex min-h-screen flex-col bg-[color:var(--insta-page-bg)]">
            @include('layouts.partials.hero-site-brand-bar', ['portalLabel' => 'Member Sign In'])

            <div class="mx-auto grid w-full max-w-6xl flex-1 grid-cols-1 items-stretch gap-6 px-4 py-8 sm:px-6 lg:grid-cols-2 lg:gap-8 lg:py-10">
                <!-- Left: brand panel -->
                <div class="hidden lg:flex lg:min-h-[560px]">
                    <div class="guest-auth-panel flex w-full flex-col overflow-hidden">
                        <div class="relative min-h-[320px] flex-1 overflow-hidden bg-[color:var(--hero-primary)]">
                            <img
                                src="{{ asset('images/banner-image.avif') }}"
                                alt="HERO emergency response vehicle"
                                class="guest-auth-hero-photo absolute inset-0 h-full w-full object-cover object-center"
                                loading="eager"
                                decoding="async"
                                fetchpriority="high"
                            >
                            <div class="absolute inset-0 bg-gradient-to-t from-[color:var(--hero-primary)] via-[color:var(--hero-primary)]/55 to-[color:var(--hero-primary)]/15"></div>
                            <div class="absolute inset-x-0 bottom-0 p-8">
                                <p class="max-w-md font-display text-lg font-semibold leading-snug text-white">
                                    One Mission, One Team.
                                </p>
                                <p class="mt-3 max-w-md text-sm leading-relaxed text-white/85">
                                    Member portal for existing HERO memberships — manage coverage, billing, and dispatch verification in one secure place.
                                </p>
                            </div>
                        </div>

                        <div class="grid shrink-0 gap-5 border-t border-slate-100 bg-white p-6">
                            <div class="flex items-start gap-3">
                                <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[color:var(--hero-primary-soft)] text-[color:var(--hero-primary)]">
                                    <i class="fa-solid fa-shield-check" aria-hidden="true"></i>
                                </span>
                                <div>
                                    <div class="font-semibold text-[color:var(--hero-primary)] font-display">Instant coverage verification</div>
                                    <p class="mt-1 text-sm text-slate-600">Dispatch confirms coverage by member name, ID, phone, or company.</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[color:var(--hero-primary-soft)] text-[color:var(--hero-primary)]">
                                    <i class="fa-solid fa-lock" aria-hidden="true"></i>
                                </span>
                                <div>
                                    <div class="font-semibold text-[color:var(--hero-primary)] font-display">Secure self-service</div>
                                    <p class="mt-1 text-sm text-slate-600">Members, businesses, and partners manage active memberships in one place.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right: auth card -->
                <div class="flex min-h-[560px] flex-col lg:min-h-0">
                    <div class="guest-auth-panel flex flex-1 flex-col justify-center p-6 sm:p-8">
                        {{ $slot }}
                    </div>
                </div>
            </div>

            <footer class="border-t border-slate-200/80 bg-white px-4 py-4 text-center text-xs text-slate-600">
                © {{ now()->year }} HERO Client Rescue S.A.
            </footer>
        </div>
    </body>
</html>
