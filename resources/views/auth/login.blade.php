<x-guest-layout>
    @php($subscribeUrl = config('heroportal.membership_subscribe_url'))

    <x-auth-session-status class="hero-alert hero-alert--success mb-6" :status="session('status')" />

    @include('auth.partials.form-header', [
        'badge' => 'Member portal access',
        'title' => __('Sign in'),
        'description' => __('This portal is for existing HERO members. Sign in to manage your membership, billing, and coverage details.'),
    ])

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input
                id="email"
                class="mt-2 block w-full"
                type="email"
                name="email"
                :value="old('email')"
                required
                autofocus
                autocomplete="username"
                placeholder="you@example.com"
            />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <div class="flex items-center justify-between gap-3">
                <x-input-label for="password" :value="__('Password')" />
                @if (Route::has('password.request'))
                    <a class="text-xs font-semibold text-[color:var(--hero-primary)] transition hover:text-[color:var(--hero-primary-hover)]"
                       href="{{ route('password.request') }}">
                        {{ __('Forgot password?') }}
                    </a>
                @endif
            </div>

            <x-text-input
                id="password"
                class="mt-2 block w-full"
                type="password"
                name="password"
                required
                autocomplete="current-password"
                placeholder="••••••••"
            />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex items-center">
            <label for="remember_me" class="inline-flex items-center gap-2">
                <input id="remember_me" type="checkbox" class="rounded-md border-slate-300 text-[color:var(--hero-primary)] shadow-sm focus:ring-[color:var(--hero-primary)]/30" name="remember">
                <span class="text-sm text-slate-600">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="pt-2">
            <x-primary-button class="guest-auth-submit w-full py-3.5 text-sm">
                {{ __('Log in') }}
            </x-primary-button>

            <div class="mt-6 rounded-2xl border border-slate-200/80 bg-slate-50/70 px-5 py-4 text-center text-sm leading-relaxed text-slate-600 backdrop-blur-sm">
                <p class="font-semibold text-slate-800">Not a member yet?</p>
                <p class="mt-1">
                    HERO memberships are purchased on our public site — accounts are not created directly in this portal.
                </p>
                <a
                    class="mt-3 inline-flex items-center justify-center gap-1.5 font-semibold text-[color:var(--hero-primary)] transition hover:text-[color:var(--hero-primary-hover)]"
                    href="{{ $subscribeUrl }}"
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    Subscribe at heroclientrescue.com
                    <i class="fa-solid fa-arrow-up-right-from-square text-[10px]" aria-hidden="true"></i>
                </a>
            </div>
        </div>
    </form>
</x-guest-layout>
