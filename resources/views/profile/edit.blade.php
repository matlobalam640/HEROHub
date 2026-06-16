@php
    $isCustomer = auth()->check() && auth()->user()->hasRole('customer');
    $initials = strtoupper(
        collect(preg_split('/\s+/', trim($user->name ?? '')))
            ->filter()
            ->take(2)
            ->map(fn ($p) => mb_substr($p, 0, 1))
            ->implode('')
    );
    $initials = $initials !== '' ? $initials : '?';
@endphp

<x-portal-layout>
    <div class="w-full max-w-6xl space-y-8">
        <div>
            <div class="text-sm font-medium text-hero-primary">{{ __('Settings') }}</div>
            <h1 class="font-display mt-1 text-2xl font-semibold tracking-tight text-slate-900">{{ __('Profile & security') }}</h1>
            <p class="mt-2 max-w-2xl text-sm text-slate-600">
                {{ __('Manage how you sign in and keep your membership account details up to date.') }}
            </p>
        </div>

        <div class="grid grid-cols-1 gap-8 lg:grid-cols-12 lg:items-start">
            <aside class="lg:col-span-4 xl:col-span-3">
                <nav class="sticky top-24 space-y-1 rounded-2xl border border-slate-200/80 bg-white p-3 shadow-sm shadow-slate-200/40 ring-1 ring-slate-100"
                    aria-label="{{ __('Profile settings sections') }}">
                    <a href="#settings-account"
                       class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold text-slate-800 transition hover:bg-slate-50">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-[color:var(--insta-teal-soft)] to-[color:var(--insta-blue-soft)] text-[color:var(--insta-teal-800)] shadow-sm ring-1 ring-slate-200/60"
                            aria-hidden="true">
                            <i class="fa-solid fa-user"></i>
                        </span>
                        <span class="min-w-0">
                            <span class="block truncate">{{ __('Account') }}</span>
                            <span class="block truncate text-xs font-normal text-slate-500">{{ __('Name & email') }}</span>
                        </span>
                    </a>
                    <a href="#settings-security"
                       class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold text-slate-800 transition hover:bg-slate-50">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-700 shadow-sm ring-1 ring-slate-200/80"
                            aria-hidden="true">
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
                <section id="settings-account" class="scroll-mt-28 overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-md shadow-slate-200/50 ring-1 ring-slate-100">
                    <div class="hero-panel-header border-b border-slate-100 bg-gradient-to-r from-slate-50 to-[color:var(--dashboard-gold-soft)] px-6 py-4">
                        <div class="flex flex-wrap items-center gap-4">
                            <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-[color:var(--insta-teal-soft)] to-[color:var(--insta-blue-soft)] text-lg font-bold tracking-tight text-[color:var(--insta-teal-800)] shadow-sm ring-1 ring-slate-200/70"
                                aria-hidden="true">
                                {{ $initials }}
                            </span>
                            <div class="min-w-0 flex-1">
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

                <section id="settings-security" class="scroll-mt-28 overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-md shadow-slate-200/50 ring-1 ring-slate-100">
                    <div class="hero-panel-header border-b border-slate-100 bg-gradient-to-r from-slate-50 to-[color:var(--dashboard-gold-soft)] px-6 py-4">
                        <div class="flex flex-wrap items-center gap-3">
                            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-slate-900 text-white shadow-sm"
                                aria-hidden="true">
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
