<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        @include('layouts.partials.favicon')

        <!-- Fonts (site-inspired) -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=montserrat:400,500,600,700&display=swap" rel="stylesheet" />
        <link href="https://fonts.bunny.net/css?family=open-sans:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased text-slate-900">
        <div class="min-h-screen bg-slate-50">
            <div class="mx-auto w-full max-w-6xl px-4 pt-4 sm:px-6 sm:pt-6 lg:px-8">
                <div class="mb-3 text-center sm:mb-4 lg:mb-6">
                    <div class="inline-flex items-center gap-3">
                        <img src="{{ asset('brand/hero-logo.png') }}" alt="HERO" class="h-10 w-auto">
                        <div class="text-left">
                            <div class="text-sm font-semibold font-display tracking-tight">HERO Membership Portal</div>
                            <div class="text-xs text-slate-500 font-['Open_Sans']">HERO Client Rescue S.A.</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mx-auto grid w-full max-w-6xl grid-cols-1 gap-0 lg:grid-cols-2">
                <!-- Left: brand panel -->
                <div class="hidden lg:flex flex-col p-10 pt-4">
                    <div>
                        <div class="relative overflow-hidden rounded-3xl">
                            <img src="{{ asset('images/banner-image.avif') }}" alt="HERO" class="h-[320px] w-full object-cover">
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-950/70 via-slate-900/20 to-transparent"></div>
                        </div>

                        <div class="mt-7 space-y-3 text-sm text-slate-600 font-['Open_Sans'] leading-relaxed">
                            <div class="flex items-start gap-3">
                                <span class="mt-0.5 inline-flex h-8 w-8 items-center justify-center rounded-xl bg-white border border-slate-200 text-hero-primary">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z"/>
                                    </svg>
                                </span>
                                <div>
                                    <div class="font-semibold text-slate-800 font-display">Instant coverage verification</div>
                                    Dispatch can confirm coverage by member name, ID, phone, or company.
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <span class="mt-0.5 inline-flex h-8 w-8 items-center justify-center rounded-xl bg-white border border-slate-200 text-hero-primary">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-4.418 0-8 1.79-8 4v6h16v-6c0-2.21-3.582-4-8-4z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 8V6a4 4 0 118 0v2"/>
                                    </svg>
                                </span>
                                <div>
                                    <div class="font-semibold text-slate-800 font-display">Secure self-service portal</div>
                                    Customers, businesses, and partners can manage memberships in one place.
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Right: auth card -->
                <div class="flex flex-col justify-center px-4 pb-6 sm:px-10 sm:pb-12 lg:pb-20">
                    <div class="mx-auto w-full max-w-md">
                        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-[0_20px_60px_-36px_rgba(15,23,42,0.35)] sm:rounded-3xl sm:p-8 sm:shadow-[0_24px_80px_-40px_rgba(15,23,42,0.35)]">
                            {{ $slot }}
                        </div>

                    </div>
                </div>
            </div>
            <div style="left: 50%; transform: translateX(-50%);" class="pointer-events-none fixed bottom-3 z-50 whitespace-nowrap rounded-full bg-white/85 px-3 py-1 text-center text-xs text-slate-700 shadow-sm ring-1 ring-slate-200/80 font-['Open_Sans']">
                © {{ now()->year }} HERO Client Rescue S.A.
            </div>
        </div>
    </body>
</html>
