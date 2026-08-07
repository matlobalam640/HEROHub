@php
    $isCustomer = auth()->check() && auth()->user()->hasRole('customer');
@endphp

<x-portal-layout>
    <div class="w-full max-w-6xl space-y-10 animate-fade-up">
        <div>
            <div class="text-xs font-bold uppercase tracking-[0.2em] text-[color:var(--hero-accent-gold)]">{{ __('Settings') }}</div>
            <h1 class="font-display mt-3 text-4xl font-bold tracking-tight text-slate-900 sm:text-5xl">{{ __('Profile & security') }}</h1>
            <p class="mt-4 max-w-2xl text-base leading-relaxed text-slate-600">
                {{ __('Manage how you sign in and keep your membership account details up to date.') }}
            </p>
        </div>

        <div class="grid grid-cols-1 gap-8 lg:grid-cols-12 lg:items-start">
            <aside class="lg:col-span-4 xl:col-span-3">
                <nav class="sticky top-28 space-y-1 rounded-2xl border border-white/70 bg-white/80 p-3 shadow-hero-card backdrop-blur-xl"
                    aria-label="{{ __('Profile settings sections') }}">
                    <a href="#settings-account"
                       class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold text-slate-800 transition hover:bg-slate-50">
                        <span class="hero-profile-section-icon hero-profile-section-icon--nav" aria-hidden="true">
                            <i class="fa-solid fa-user"></i>
                        </span>
                        <span class="min-w-0">
                            <span class="block truncate">{{ __('Account') }}</span>
                            <span class="block truncate text-xs font-normal text-slate-500">{{ __('Name & email') }}</span>
                        </span>
                    </a>
                    <a href="#settings-security"
                       class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold text-slate-800 transition hover:bg-slate-50">
                        <span class="hero-profile-section-icon hero-profile-section-icon--nav" aria-hidden="true">
                            <i class="fa-solid fa-lock"></i>
                        </span>
                        <span class="min-w-0">
                            <span class="block truncate">{{ __('Security') }}</span>
                            <span class="block truncate text-xs font-normal text-slate-500">{{ __('Password') }}</span>
                        </span>
                    </a>
                </nav>
            </aside>

            <div class="min-w-0 space-y-6 lg:col-span-8 xl:col-span-9">
                <section id="settings-account" class="scroll-mt-28 hero-portal-panel">
                    <div class="hero-panel-header border-b border-slate-100 px-6 py-4">
                        <div class="flex flex-wrap items-center gap-3">
                            <span class="hero-profile-section-icon hero-profile-section-icon--panel" aria-hidden="true">
                                <i class="fa-solid fa-user"></i>
                            </span>
                            <div class="min-w-0">
                                <h2 class="text-base font-semibold text-slate-900">{{ __('Account') }}</h2>
                                <p class="mt-0.5 text-sm text-slate-600">
                                    @if($isCustomer)
                                        {{ __('Name and email are read-only for customer accounts. Contact support if you need an update.') }}
                                    @else
                                        {{ __("Update your account's profile information and email address.") }}
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="p-6 text-slate-900 sm:p-8">
                        @if($isCustomer)
                            <div class="grid max-w-xl grid-cols-1 gap-6 sm:max-w-none sm:grid-cols-2">
                                <div class="sm:col-span-2">
                                    <x-input-label for="name" :value="__('Name')" />
                                    <x-text-input id="name" type="text" class="mt-1.5 block w-full bg-slate-50" :value="$user->name" disabled />
                                </div>
                                <div class="sm:col-span-2">
                                    <x-input-label for="email" :value="__('Email')" />
                                    <x-text-input id="email" type="email" class="mt-1.5 block w-full bg-slate-50" :value="$user->email" disabled />
                                </div>
                            </div>
                        @else
                            @include('profile.partials.update-profile-information-form', ['hideHeader' => true])
                        @endif
                    </div>
                </section>

                <section id="settings-security" class="scroll-mt-28 hero-portal-panel">
                    <div class="hero-panel-header border-b border-slate-100 px-6 py-4">
                        <div class="flex flex-wrap items-center gap-3">
                            <span class="hero-profile-section-icon hero-profile-section-icon--panel" aria-hidden="true">
                                <i class="fa-solid fa-key"></i>
                            </span>
                            <div class="min-w-0">
                                <h2 class="text-base font-semibold text-slate-900">{{ __('Password') }}</h2>
                                <p class="mt-0.5 text-sm text-slate-600">{{ __('Use a long, unique password to protect your account.') }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="p-6 text-slate-900 sm:p-8">
                        <div class="max-w-xl sm:max-w-2xl">
                            @include('profile.partials.update-password-form', ['hideHeader' => true])
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</x-portal-layout>
