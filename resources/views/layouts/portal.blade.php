<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'HERO Membership Portal') }}</title>

        @include('layouts.partials.favicon')

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|montserrat:500,600,700|open-sans:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @include('layouts.partials.portal-sidebar-styles')
        <style id="hero-portal-topbar-styles">
            html, body {
                margin: 0;
                border: 0 !important;
                outline: 0 !important;
                box-shadow: none !important;
            }
            .min-h-screen {
                border: 0 !important;
                outline: 0 !important;
            }
            /* Sidebar tokens: light values live on :root in app.css; dark must override or borders/text stay paper-white / ink-navy */
            html.dark-theme {
                --sidebar-surface: #25293c;
                --sidebar-border: #3b4253;
                --sidebar-text: #b4b7bd;
                --sidebar-text-strong: #e7eaf0;
                --sidebar-section: #7c7f8e;
                --sidebar-menu-active-bg: rgba(115, 103, 240, 0.15);
                --sidebar-menu-active-bg-hover: rgba(115, 103, 240, 0.22);
            }
            html.dark-theme .portal-sidebar .sidebar-brand img {
                filter: brightness(1.08) contrast(1.02);
            }
            /* Vuexy Bootstrap dark-layout palette (Analytics-style) */
            .dark-theme body {
                background: #25293c !important;
                color: #d0d2d6;
            }
            .dark-theme .portal-sidebar {
                background: #25293c !important;
                border-right-color: #3b4253 !important;
            }
            .dark-theme .portal-sidebar .sidebar-brand,
            .dark-theme .portal-sidebar .sidebar-footer {
                background: #25293c !important;
                border-color: #3b4253 !important;
            }
            .dark-theme header {
                background: #2f3349 !important;
                border-color: #3b4253 !important;
                box-shadow: 0 4px 24px -12px rgba(0, 0, 0, 0.45) !important;
            }
            .dark-theme main {
                color: #d0d2d6;
                background: transparent;
            }
            /* Module header strip (dashboard, coming-soon): html.dark-theme does not enable Tailwind dark: variants */
            .dark-theme .hero-portal-page-header {
                background-image: linear-gradient(180deg, #2f3349 0%, #2a2e42 100%) !important;
                border-color: #3b4253 !important;
                --tw-ring-color: rgba(59, 66, 83, 0.65) !important;
                box-shadow: 0 4px 24px -14px rgba(0, 0, 0, 0.5) !important;
            }
            .dark-theme .hero-portal-page-header h1 {
                color: #e7eaf0 !important;
            }
            .dark-theme .hero-portal-page-header .text-slate-400 {
                color: #7c7f8e !important;
            }
            .dark-theme .hero-portal-page-header__metric {
                background-color: #25293c !important;
                border-color: #3b4253 !important;
            }
            .dark-theme .hero-portal-page-header__metric .text-slate-500 {
                color: #b4b7bd !important;
            }
            .dark-theme .hero-portal-page-header__metric .text-slate-900,
            .dark-theme .hero-portal-page-header__metric .text-slate-100 {
                color: #e7eaf0 !important;
            }
            .dark-theme .hero-portal-page-header__action {
                background-color: #25293c !important;
                border-color: #3b4253 !important;
                color: #e7eaf0 !important;
            }
            .dark-theme .hero-portal-page-header__action:hover {
                background-color: #363a52 !important;
            }
            .dark-theme .bg-white {
                background-color: #2f3349 !important;
            }
            .dark-theme .bg-slate-50,
            .dark-theme .bg-slate-50\/80 {
                background-color: rgba(47, 51, 73, 0.85) !important;
            }
            .dark-theme .hover\:bg-slate-50:hover,
            .dark-theme .hover\:bg-slate-100:hover {
                background-color: rgba(59, 66, 83, 0.55) !important;
            }
            .dark-theme .border-slate-200,
            .dark-theme .border-slate-200\/70,
            .dark-theme .border-slate-200\/80,
            .dark-theme .border-slate-100 {
                border-color: #3b4253 !important;
            }
            .hero-topbar-popover {
                background: #fff;
            }
            .dark-theme .hero-topbar-popover {
                background: #2f3349;
                border-color: #3b4253 !important;
                color: #d0d2d6;
                box-shadow: 0 12px 40px -16px rgba(0, 0, 0, 0.55) !important;
            }
            .dark-theme .hero-topbar-popover a:hover {
                background: rgba(115, 103, 240, 0.12) !important;
            }
            .dark-theme .text-slate-900,
            .dark-theme .text-slate-800 {
                color: #e7eaf0 !important;
            }
            .dark-theme .text-slate-700,
            .dark-theme .text-slate-600 {
                color: #d0d2d6 !important;
            }
            .dark-theme .text-slate-500 {
                color: #b4b7bd !important;
            }
            .dark-theme .text-hero-primary {
                color: #a59cec !important;
            }
            .dark-theme .hero-panel-header,
            .dark-theme .dashboard-card-header {
                background-image: linear-gradient(180deg, #2f3349 0%, #2c3044 100%) !important;
                border-bottom-color: #3b4253 !important;
                box-shadow: 0 1px 0 rgba(255, 255, 255, 0.04) inset !important;
            }
            .dark-theme .dashboard-card-header__title {
                color: #e7eaf0 !important;
            }
            .dark-theme .dashboard-card-header__sub {
                color: #b4b7bd !important;
            }
            /* Active nav: Vuexy primary (overridden in sidebar partial for specificity) */
            .dark-theme .portal-sidebar .sidebar-link--pill.sidebar-link-active,
            .dark-theme .portal-sidebar .sidebar-link--pill[aria-current="page"],
            .dark-theme .portal-sidebar .sidebar-link--pill[data-nav-active],
            .dark-theme .portal-sidebar .sidebar-link:not(.sidebar-link--pill).sidebar-link-active,
            .dark-theme .portal-sidebar .sidebar-link:not(.sidebar-link--pill)[aria-current="page"],
            .dark-theme .portal-sidebar .sidebar-link:not(.sidebar-link--pill)[data-nav-active] {
                background-color: #7367f0 !important;
                background-image: none !important;
                box-shadow: 0 8px 22px -8px rgba(115, 103, 240, 0.55) !important;
            }
            .dark-theme .portal-sidebar a.sidebar-link--pill.sidebar-link-active:hover,
            .dark-theme .portal-sidebar a.sidebar-link--pill[aria-current="page"]:hover,
            .dark-theme .portal-sidebar a.sidebar-link--pill[data-nav-active]:hover,
            .dark-theme .portal-sidebar a.sidebar-link:not(.sidebar-link--pill).sidebar-link-active:hover,
            .dark-theme .portal-sidebar a.sidebar-link:not(.sidebar-link--pill)[aria-current="page"]:hover,
            .dark-theme .portal-sidebar a.sidebar-link:not(.sidebar-link--pill)[data-nav-active]:hover {
                background-color: #6558d3 !important;
            }
            .dark-theme header input[type="search"] {
                background: #25293c !important;
                border-color: #3b4253 !important;
                color: #e7eaf0 !important;
            }
            .dark-theme header input[type="search"]:focus {
                background: #2c3145 !important;
                border-color: #7367f0 !important;
            }
            .dark-theme header input[type="search"]::placeholder {
                color: #7c7f8e !important;
            }
            .dark-theme .shadow-\[0_2px_12px_-4px_rgba\(15\,23\,42\,0\.06\)\] {
                box-shadow: 0 4px 24px -10px rgba(0, 0, 0, 0.5) !important;
            }
            .dark-theme .shadow-sm {
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.28) !important;
            }
            .dark-theme .shadow-md {
                box-shadow: 0 6px 16px rgba(0, 0, 0, 0.32) !important;
            }
            .dark-theme .ring-slate-100,
            .dark-theme .ring-slate-200\/60,
            .dark-theme .ring-slate-200\/80 {
                --tw-ring-color: rgba(59, 66, 83, 0.65) !important;
            }
            .dark-theme .hero-datatable .datatable-top,
            .dark-theme .hero-datatable .datatable-bottom {
                background: #2c3044 !important;
                border-color: #3b4253 !important;
            }
            .dark-theme .hero-datatable .datatable-container {
                background: #2f3349 !important;
                border-color: #3b4253 !important;
            }
            .dark-theme .hero-datatable table.datatable-table thead th {
                background: #343752 !important;
                color: #e7eaf0 !important;
                border-color: #3b4253 !important;
            }
            .dark-theme .hero-datatable table.datatable-table tbody td {
                color: #d0d2d6 !important;
                border-color: #3b4253 !important;
            }
            .dark-theme .hero-datatable table.datatable-table tbody tr:hover {
                background: rgba(115, 103, 240, 0.08) !important;
            }
            .dark-theme .hero-datatable .datatable-input,
            .dark-theme .hero-datatable .datatable-selector {
                background: #25293c !important;
                color: #e7eaf0 !important;
                border-color: #3b4253 !important;
            }
            .dark-theme .hero-datatable .datatable-info {
                color: #b4b7bd !important;
            }
            .dark-theme .dashboard-stat-card {
                background: linear-gradient(165deg, #2f3349 0%, #2a2e42 100%) !important;
                border-color: #3b4253 !important;
                box-shadow: 0 8px 28px -12px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(255, 255, 255, 0.04) inset !important;
            }
            .dark-theme .dashboard-stat-card .text-slate-500 {
                color: #b4b7bd !important;
            }
            .dark-theme .dashboard-stat-card .text-slate-900 {
                color: #e7eaf0 !important;
            }
            .dark-theme .membership-digital-card {
                background: #e4e4e8 !important;
                color: #020617 !important;
            }
            .dark-theme .membership-digital-card .text-slate-950,
            .dark-theme .membership-digital-card .text-slate-900 {
                color: #020617 !important;
            }
            .dark-theme .membership-digital-card .text-slate-700 {
                color: #cbd5e1 !important;
            }
            .dark-theme .membership-digital-card .bg-white {
                background: #ffffff !important;
            }
            .dark-theme .membership-digital-card .ring-slate-200 {
                --tw-ring-color: rgb(226 232 240) !important;
            }
        </style>
    </head>
    <body class="min-h-screen bg-[color:var(--insta-page-bg)] font-sans antialiased text-slate-900" style="font-family: Inter, ui-sans-serif, system-ui, sans-serif">
        @php
            $portalHomeUrl = route('dashboard');
            $portalHomeLabel = 'Dashboard';
            if (auth()->check() && \App\Providers\RouteServiceProvider::isCustomerPortalOnly(auth()->user())) {
                $portalHomeUrl = route('customer.membership');
                $portalHomeLabel = 'My membership';
            } elseif (auth()->check() && \App\Providers\RouteServiceProvider::isBusinessPortalPrimary(auth()->user())) {
                $portalHomeUrl = route('business.portal');
                $portalHomeLabel = 'Company portal';
            }
        @endphp
        <div
            x-data="{
                sidebarOpen: false,
                sidebarCollapsed: false,
                languageOpen: false,
                notificationOpen: false,
                searchOpen: false,
                theme: 'light',
                locale: 'en',
                searchQuery: '',
                notifications: [
                    { id: 1, title: 'New membership submitted', when: '2m ago', read: false },
                    { id: 2, title: 'Partner payout updated', when: '1h ago', read: false },
                    { id: 3, title: 'Coverage verification completed', when: 'Today', read: true },
                ],
                searchItems: [
                    { label: '{{ $portalHomeLabel }}', url: '{{ $portalHomeUrl }}' },
                    { label: 'Reports', url: '{{ route('portal.coming-soon', ['page' => 'reports']) }}' },
                    { label: 'My Membership', url: '{{ route('customer.membership') }}' },
                    { label: 'Customers', url: '{{ route('portal.coming-soon', ['page' => 'customers']) }}' },
                    { label: 'Coverage Verification', url: '{{ route('dispatch.verification') }}' },
                    { label: 'Memberships', url: '{{ route('portal.coming-soon', ['page' => 'memberships']) }}' },
                    { label: 'Retail Membership Plans', url: '{{ route('portal.plans.retail') }}' },
                    { label: 'Small Business Plans', url: '{{ route('portal.plans.small-business') }}' },
                    { label: 'Corporate Plans', url: '{{ route('portal.plans.corporate') }}' },
                    { label: 'Companies', url: '{{ route('portal.coming-soon', ['page' => 'companies']) }}' },
                    { label: 'Partners', url: '{{ route('portal.coming-soon', ['page' => 'partners']) }}' },
                    { label: 'Settings', url: '{{ route('portal.coming-soon', ['page' => 'settings']) }}' },
                    { label: 'Profile', url: '{{ route('profile.edit') }}' },
                ],
                init() {
                    try {
                        this.sidebarCollapsed = localStorage.getItem('hero_portal_sidebar_collapsed') === '1';
                        this.theme = localStorage.getItem('hero_portal_theme') || 'light';
                        this.locale = localStorage.getItem('hero_portal_locale') || 'en';
                        this.notifications = JSON.parse(localStorage.getItem('hero_portal_notifications') || JSON.stringify(this.notifications));
                    } catch (e) {}
                    this.applyTheme();
                },
                toggleSidebarCollapse() {
                    this.sidebarCollapsed = !this.sidebarCollapsed;
                    try {
                        localStorage.setItem('hero_portal_sidebar_collapsed', this.sidebarCollapsed ? '1' : '0');
                    } catch (e) {}
                },
                setLocale(nextLocale) {
                    this.locale = nextLocale;
                    this.languageOpen = false;
                    document.documentElement.lang = nextLocale;
                    try {
                        localStorage.setItem('hero_portal_locale', nextLocale);
                    } catch (e) {}
                },
                toggleTheme() {
                    this.theme = this.theme === 'dark' ? 'light' : 'dark';
                    this.applyTheme();
                    try {
                        localStorage.setItem('hero_portal_theme', this.theme);
                    } catch (e) {}
                },
                applyTheme() {
                    document.documentElement.classList.toggle('dark-theme', this.theme === 'dark');
                },
                markAllNotificationsRead() {
                    this.notifications = this.notifications.map((item) => ({ ...item, read: true }));
                    try {
                        localStorage.setItem('hero_portal_notifications', JSON.stringify(this.notifications));
                    } catch (e) {}
                },
                searchResults() {
                    const q = this.searchQuery.trim().toLowerCase();
                    if (!q) return [];
                    return this.searchItems.filter((item) => item.label.toLowerCase().includes(q)).slice(0, 8);
                },
                submitGlobalSearch() {
                    const results = this.searchResults();
                    if (results.length) {
                        window.location.href = results[0].url;
                    }
                },
            }"
            class="min-h-screen"
        >
            <div class="lg:hidden">
                <div x-show="sidebarOpen" x-cloak class="fixed inset-0 z-40 bg-black/40" x-on:click="sidebarOpen = false"></div>
                <div
                    x-show="sidebarOpen"
                    x-cloak
                    x-transition:enter="transition transform ease-out duration-200"
                    x-transition:enter-start="-translate-x-full"
                    x-transition:enter-end="translate-x-0"
                    class="portal-sidebar fixed inset-y-0 left-0 z-50 w-72 shadow-xl shadow-slate-900/10"
                >
                    @include('layouts.partials.sidebar', ['mobile' => true])
                </div>
            </div>

            <div
                class="portal-sidebar hidden lg:fixed lg:inset-y-0 lg:z-40 lg:flex lg:flex-col transition-[width] duration-200 ease-out"
                :class="sidebarCollapsed ? 'lg:w-[88px] portal-sidebar--collapsed' : 'lg:w-72'"
            >
                @include('layouts.partials.sidebar', ['mobile' => false])
            </div>

            <div class="transition-[padding] duration-200 ease-out" :class="sidebarCollapsed ? 'lg:pl-[88px]' : 'lg:pl-72'">
                <header class="sticky top-0 z-30 border-b border-slate-200/70 bg-white shadow-[0_2px_12px_-4px_rgba(15,23,42,0.06)]">
                    <div class="flex h-[4.25rem] items-center gap-3 px-4 sm:px-6 lg:px-8">
                        <button
                            type="button"
                            class="lg:hidden inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 text-base text-slate-700 hover:bg-slate-50"
                            x-on:click="sidebarOpen = true"
                            aria-label="Open menu"
                        >
                            <i class="fa-solid fa-bars" aria-hidden="true"></i>
                        </button>

                        <a
                            href="{{ $portalHomeUrl }}"
                            class="flex shrink-0 items-center lg:hidden"
                            aria-label="{{ config('app.name', 'HERO') }} — home"
                        >
                            <img
                                src="{{ asset('brand/hero-logo.png') }}"
                                alt=""
                                class="h-8 w-auto max-w-[7.5rem] object-contain object-left"
                                width="120"
                                height="32"
                                loading="eager"
                                decoding="async"
                            />
                        </a>

                        <button
                            type="button"
                            class="hidden lg:inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 text-base text-slate-700 hover:bg-slate-50"
                            x-on:click="toggleSidebarCollapse()"
                            :title="sidebarCollapsed ? 'Expand sidebar' : 'Collapse sidebar'"
                            aria-label="Toggle sidebar size"
                        >
                            <i class="fa-solid fa-table-columns" aria-hidden="true"></i>
                        </button>

                        <div class="flex flex-1 items-center justify-center px-2">
                            <div class="relative w-full max-w-2xl" @click.outside="searchOpen = false">
                                <i class="fa-solid fa-magnifying-glass pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-slate-400" aria-hidden="true"></i>
                                <input
                                    type="search"
                                    name="portal_search"
                                    placeholder="Search here…"
                                    x-model="searchQuery"
                                    x-on:focus="searchOpen = true"
                                    x-on:keydown.enter.prevent="submitGlobalSearch()"
                                    class="w-full rounded-full border border-slate-200/90 bg-slate-50 py-2.5 pl-10 pr-4 text-sm text-slate-800 placeholder:text-slate-400 focus:border-[color:var(--insta-teal)] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[color:var(--insta-teal)]/25 focus:ring-offset-0"
                                />
                                <div x-cloak x-show="searchOpen && searchResults().length" class="hero-topbar-popover absolute left-0 right-0 z-50 mt-2 overflow-hidden rounded-2xl border border-slate-200 shadow-xl">
                                    <template x-for="item in searchResults()" :key="item.url">
                                        <a :href="item.url" class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50">
                                            <i class="fa-solid fa-magnifying-glass text-xs text-slate-400"></i>
                                            <span x-text="item.label"></span>
                                        </a>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <div class="flex shrink-0 items-center gap-1 sm:gap-2">
                            <div class="relative hidden sm:block" @click.outside="languageOpen = false">
                                <button type="button" class="inline-flex h-10 items-center justify-center gap-2 rounded-xl px-3 text-[color:var(--insta-orange)] hover:bg-slate-100" title="Language" aria-label="Language" x-on:click="languageOpen = !languageOpen">
                                    <i class="fa-solid fa-language text-lg" aria-hidden="true"></i>
                                    <span class="text-xs font-semibold uppercase text-slate-700" x-text="locale"></span>
                                </button>
                                <div x-cloak x-show="languageOpen" class="hero-topbar-popover absolute right-0 z-50 mt-2 w-40 overflow-hidden rounded-xl border border-slate-200 shadow-xl">
                                    <button type="button" class="block w-full px-3 py-2 text-left text-sm hover:bg-slate-50" x-on:click="setLocale('en')">English</button>
                                    <button type="button" class="block w-full px-3 py-2 text-left text-sm hover:bg-slate-50" x-on:click="setLocale('fr')">Francais</button>
                                    <button type="button" class="block w-full px-3 py-2 text-left text-sm hover:bg-slate-50" x-on:click="setLocale('es')">Espanol</button>
                                </div>
                            </div>
                            <button type="button" class="hidden h-10 w-10 items-center justify-center rounded-xl text-slate-500 hover:bg-slate-100 hover:text-slate-800 md:inline-flex" title="Theme" aria-label="Theme" x-on:click="toggleTheme()">
                                <i class="fa-solid text-lg" :class="theme === 'dark' ? 'fa-moon' : 'fa-sun'" aria-hidden="true"></i>
                            </button>
                            <div class="relative hidden sm:block" @click.outside="notificationOpen = false">
                                <button type="button" class="relative inline-flex h-10 w-10 items-center justify-center rounded-xl text-[color:var(--insta-purple)] hover:bg-slate-100" title="Notifications" aria-label="Notifications" x-on:click="notificationOpen = !notificationOpen">
                                    <i class="fa-regular fa-bell text-lg" aria-hidden="true"></i>
                                    <span x-show="notifications.some((item) => !item.read)" class="absolute right-2 top-2 h-2 w-2 rounded-full bg-[color:var(--insta-orange)] ring-2 ring-white"></span>
                                </button>
                                <div x-cloak x-show="notificationOpen" style="width: 260px; min-width: 260px;" class="hero-topbar-popover absolute right-0 z-50 mt-2 max-w-[calc(100vw-1rem)] overflow-hidden rounded-2xl border border-slate-200 shadow-xl">
                                    <div class="flex items-center gap-3 border-b border-slate-200 px-4 py-3">
                                        <div class="text-base font-semibold leading-tight">Notifications</div>
                                        <button type="button" class="whitespace-nowrap rounded-lg px-2 py-1 text-xs font-semibold text-hero-primary hover:bg-slate-100" x-on:click="markAllNotificationsRead()">Mark all read</button>
                                    </div>
                                    <div class="max-h-72 overflow-y-auto">
                                        <template x-for="note in notifications" :key="note.id">
                                            <div class="border-b border-slate-100 px-4 py-3 last:border-b-0">
                                                <div class="flex items-start gap-2">
                                                    <span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-[color:var(--insta-orange)]" x-show="!note.read"></span>
                                                    <p class="text-sm font-medium leading-6 text-slate-800" x-text="note.title"></p>
                                                </div>
                                                <p class="mt-1 pl-4 text-xs text-slate-500" x-text="note.when"></p>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                            <div class="mx-1 hidden h-8 w-px bg-slate-200 sm:block" aria-hidden="true"></div>
                            <div class="hidden text-right lg:block" :class="sidebarCollapsed ? 'xl:hidden' : ''">
                                <div class="text-sm font-semibold leading-4 text-slate-800">{{ auth()->user()->name }}</div>
                            </div>
                            <x-dropdown align="right" width="48">
                                <x-slot name="trigger">
                                    <button class="relative inline-flex h-10 items-center gap-2 rounded-xl border border-slate-200 bg-white px-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50">
                                        <span class="relative flex h-9 w-9 items-center justify-center rounded-full bg-gradient-to-br from-[color:var(--insta-teal-soft)] to-[color:var(--insta-blue-soft)] text-[color:var(--insta-teal-800)]">
                                            <i class="fa-solid fa-user text-sm" aria-hidden="true"></i>
                                            <span class="absolute bottom-0 right-0 h-2.5 w-2.5 rounded-full bg-emerald-500 ring-2 ring-white dark:ring-[#2f3349]" title="Online"></span>
                                        </span>
                                        <i class="fa-solid fa-chevron-down hidden pr-1 text-xs text-slate-500 sm:inline" aria-hidden="true"></i>
                                    </button>
                                </x-slot>
                                <x-slot name="content">
                                    <x-dropdown-link :href="route('profile.edit')">
                                        {{ __('Profile') }}
                                    </x-dropdown-link>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <x-dropdown-link :href="route('logout')"
                                            onclick="event.preventDefault(); this.closest('form').submit();">
                                            {{ __('Log Out') }}
                                        </x-dropdown-link>
                                    </form>
                                </x-slot>
                            </x-dropdown>
                        </div>
                    </div>
                </header>

                <main class="px-4 py-4 sm:px-6 sm:py-5 lg:px-8">
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
