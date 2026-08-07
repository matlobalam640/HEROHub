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
        <script>
            document.documentElement.classList.remove('dark-theme');
            try { localStorage.removeItem('hero_portal_theme'); } catch (e) {}
        </script>
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
            .hero-topbar-popover {
                background: #fff;
            }
        </style>
    </head>
    <body class="min-h-screen font-sans antialiased text-slate-900" style="font-family: 'Open Sans', Inter, ui-sans-serif, system-ui, sans-serif">
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
                locale: 'en',
                searchQuery: '',
                searchLoading: false,
                searchDataResults: [],
                searchDebounce: null,
                canSearchLiveData: {{ auth()->user()->hasAnyRole(['admin', 'dispatch']) ? 'true' : 'false' }},
                searchEndpoint: '{{ route('portal.search') }}',
                verificationSearchUrl: '{{ route('dispatch.verification') }}',
                navSearchItems: [
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
                notifications: [
                    { id: 1, title: 'New membership submitted', when: '2m ago', read: false },
                    { id: 2, title: 'Partner payout updated', when: '1h ago', read: false },
                    { id: 3, title: 'Coverage verification completed', when: 'Today', read: true },
                ],
                init() {
                    try {
                        this.sidebarCollapsed = localStorage.getItem('hero_portal_sidebar_collapsed') === '1';
                        this.locale = localStorage.getItem('hero_portal_locale') || 'en';
                        this.notifications = JSON.parse(localStorage.getItem('hero_portal_notifications') || JSON.stringify(this.notifications));
                        localStorage.removeItem('hero_portal_theme');
                    } catch (e) {}
                    document.documentElement.classList.remove('dark-theme');
                    this.$watch('searchQuery', (value) => {
                        clearTimeout(this.searchDebounce);
                        const q = (value || '').trim();
                        if (q.length < 2 || ! this.canSearchLiveData) {
                            this.searchDataResults = [];
                            this.searchLoading = false;
                            return;
                        }
                        this.searchLoading = true;
                        this.searchDebounce = setTimeout(() => this.fetchLiveSearch(), 280);
                    });
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
                markAllNotificationsRead() {
                    this.notifications = this.notifications.map((item) => ({ ...item, read: true }));
                    try {
                        localStorage.setItem('hero_portal_notifications', JSON.stringify(this.notifications));
                    } catch (e) {}
                },
                navSearchResults() {
                    const q = this.searchQuery.trim().toLowerCase();
                    if (! q) {
                        return [];
                    }

                    return this.navSearchItems.filter((item) => item.label.toLowerCase().includes(q)).slice(0, 5);
                },
                combinedSearchResults() {
                    const seen = new Set();
                    const merged = [];

                    for (const item of [...this.searchDataResults, ...this.navSearchResults()]) {
                        if (seen.has(item.url)) {
                            continue;
                        }
                        seen.add(item.url);
                        merged.push(item);
                        if (merged.length >= 10) {
                            break;
                        }
                    }

                    return merged;
                },
                async fetchLiveSearch() {
                    const q = this.searchQuery.trim();
                    if (q.length < 2 || ! this.canSearchLiveData) {
                        this.searchLoading = false;
                        return;
                    }

                    try {
                        const response = await fetch(`${this.searchEndpoint}?q=${encodeURIComponent(q)}`, {
                            headers: {
                                Accept: 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                        });

                        if (response.ok) {
                            const payload = await response.json();
                            this.searchDataResults = payload.results || [];
                        }
                    } catch (error) {
                        this.searchDataResults = [];
                    }

                    this.searchLoading = false;
                },
                submitGlobalSearch() {
                    const results = this.combinedSearchResults();
                    if (results.length) {
                        window.location.href = results[0].url;
                        return;
                    }

                    const q = this.searchQuery.trim();
                    if (this.canSearchLiveData && q.length >= 2) {
                        window.location.href = `${this.verificationSearchUrl}?q=${encodeURIComponent(q)}`;
                    }
                },
                searchIconClass(kind) {
                    if (kind === 'membership') {
                        return 'fa-solid fa-id-card';
                    }
                    if (kind === 'customer') {
                        return 'fa-solid fa-user';
                    }

                    return 'fa-solid fa-link';
                },
            }"
            class="hero-portal-canvas flex min-h-screen flex-col"
        >
            @include('layouts.partials.hero-site-brand-bar', ['portalLabel' => 'Membership Portal'])

            <div class="hero-portal-body min-h-0 flex-1">
            <div class="lg:hidden">
                <div x-show="sidebarOpen" x-cloak class="fixed inset-0 z-40 bg-black/40" x-on:click="sidebarOpen = false"></div>
                <div
                    x-show="sidebarOpen"
                    x-cloak
                    x-transition:enter="transition transform ease-out duration-200"
                    x-transition:enter-start="-translate-x-full"
                    x-transition:enter-end="translate-x-0"
                    class="portal-sidebar fixed bottom-0 left-0 top-[4.1875rem] z-50 w-72 shadow-xl shadow-slate-900/10"
                >
                    @include('layouts.partials.sidebar', ['mobile' => true])
                </div>
            </div>

            <div
                class="portal-sidebar hidden lg:fixed lg:bottom-0 lg:top-[4.1875rem] lg:z-40 lg:flex lg:flex-col transition-[width] duration-200 ease-out"
                :class="sidebarCollapsed ? 'lg:w-[88px] portal-sidebar--collapsed' : 'lg:w-72'"
            >
                @include('layouts.partials.sidebar', ['mobile' => false])
            </div>

            <div class="hero-portal-shell min-w-0 flex-1 transition-[padding] duration-200 ease-out" :class="sidebarCollapsed ? 'lg:pl-[88px]' : 'lg:pl-72'">
                <header class="hero-portal-topbar">
                    <div class="flex h-16 items-center gap-2 px-4 sm:px-6 lg:px-8">
                        <button
                            type="button"
                            class="hero-topbar-icon-btn lg:hidden"
                            x-on:click="sidebarOpen = true"
                            aria-label="Open menu"
                        >
                            <i class="fa-solid fa-bars" aria-hidden="true"></i>
                        </button>

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
                                    x-on:input="searchOpen = true"
                                    x-on:keydown.enter.prevent="submitGlobalSearch()"
                                    class="w-full rounded-full border border-slate-200/90 bg-slate-50 py-2.5 pl-10 pr-4 text-sm text-slate-800 placeholder:text-slate-400 focus:border-[color:var(--hero-accent-gold)] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[color:var(--hero-accent-gold-ring)] focus:ring-offset-0"
                                />
                                <div
                                    x-cloak
                                    x-show="searchOpen && (searchLoading || combinedSearchResults().length || (canSearchLiveData && searchQuery.trim().length >= 2) || navSearchResults().length)"
                                    class="hero-topbar-popover absolute left-0 right-0 z-50 mt-2 overflow-hidden rounded-2xl border border-slate-200 shadow-xl"
                                >
                                    <div x-show="searchLoading" class="px-4 py-3 text-sm text-slate-500">
                                        Searching…
                                    </div>
                                    <template x-for="item in combinedSearchResults()" :key="item.url">
                                        <a :href="item.url" class="flex items-start gap-3 border-b border-slate-100 px-4 py-2.5 text-sm text-slate-700 last:border-b-0 hover:bg-slate-50">
                                            <i class="mt-0.5 text-xs text-[color:var(--hero-accent-gold)]" :class="searchIconClass(item.kind)" aria-hidden="true"></i>
                                            <span class="min-w-0">
                                                <span class="block truncate font-medium text-slate-900" x-text="item.label"></span>
                                                <span x-show="item.meta" class="block truncate text-xs text-slate-500" x-text="item.meta"></span>
                                            </span>
                                        </a>
                                    </template>
                                    <div
                                        x-show="! searchLoading && ! combinedSearchResults().length && searchQuery.trim().length >= 2 && canSearchLiveData"
                                        class="px-4 py-3 text-sm text-slate-600"
                                    >
                                        No quick matches. Press <kbd class="rounded border border-slate-200 bg-slate-50 px-1.5 py-0.5 text-xs font-semibold">Enter</kbd> to open coverage verification.
                                    </div>
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
                            <div class="relative hidden sm:block" @click.outside="notificationOpen = false">
                                <button type="button" class="hero-topbar-icon-btn relative" title="Notifications" aria-label="Notifications" x-on:click="notificationOpen = !notificationOpen">
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
                                    <button class="hero-topbar-icon-btn relative inline-flex h-10 items-center gap-2 px-2 sm:px-2.5">
                                        <span class="relative flex h-9 w-9 items-center justify-center rounded-full text-[color:var(--hero-primary)]" style="background: var(--gradient-gold-soft);">
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

                <main class="hero-portal-main">
                    <div class="hero-portal-content">
                    @auth
                        @include('customer.membership.partials.coverage-incomplete-banner')
                    @endauth
                    {{ $slot }}
                    </div>
                </main>
            </div>
            </div>
        </div>
        @stack('scripts')
    </body>
</html>
