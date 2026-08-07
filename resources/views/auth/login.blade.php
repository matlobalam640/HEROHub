<x-guest-layout>
    @php($subscribeUrl = config('heroportal.membership_subscribe_url'))

    <!-- Session Status -->
    <x-auth-session-status class="mb-6" :status="session('status')" />

    <div class="mb-7">
        <div class="inline-flex items-center gap-2 rounded-full bg-[color:var(--hero-primary-soft)] px-3 py-1 text-xs font-semibold text-[color:var(--hero-primary)]">
            <span class="inline-flex h-2 w-2 rounded-full bg-[color:var(--hero-primary)]"></span>
            Member portal access
        </div>
        <h1 class="mt-3 text-3xl font-semibold tracking-tight font-display">
            Sign in
        </h1>
        <p class="mt-2 text-sm text-slate-600 font-['Open_Sans'] leading-relaxed">
            This portal is for existing HERO members. Sign in to manage your membership, billing, and coverage details.
        </p>
    </div>

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
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

        <!-- Password -->
        <div class="mt-5">
            <div class="flex items-center justify-between">
                <x-input-label for="password" :value="__('Password')" />
                @if (Route::has('password.request'))
                    <a class="text-xs font-semibold text-[color:var(--hero-primary)] hover:text-[color:var(--hero-primary-hover)]"
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

        <!-- Remember Me -->
        <div class="mt-5 flex items-center justify-between">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded-lg border-slate-300 text-[color:var(--hero-primary)] shadow-sm focus:ring-[color:var(--hero-primary)]" name="remember">
                <span class="ms-2 text-sm text-slate-600">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="mt-7">
            <x-primary-button class="guest-auth-submit w-full py-3 text-sm normal-case tracking-normal rounded-2xl">
                {{ __('Log in') }}
            </x-primary-button>

            <div class="mt-5 rounded-2xl border border-slate-200/80 bg-slate-50/80 px-4 py-3 text-center text-xs leading-relaxed text-slate-600 font-['Open_Sans']">
                <p class="font-semibold text-slate-700">Not a member yet?</p>
                <p class="mt-1">
                    HERO memberships are purchased on our public site — accounts are not created directly in this portal.
                </p>
                <a
                    class="mt-2 inline-flex items-center justify-center font-semibold text-[color:var(--hero-primary)] hover:text-[color:var(--hero-primary-hover)]"
                    href="{{ $subscribeUrl }}"
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    Subscribe at heroclientrescue.com
                    <i class="fa-solid fa-arrow-up-right-from-square ms-1.5 text-[10px]" aria-hidden="true"></i>
                </a>
            </div>
        </div>
    </form>
</x-guest-layout>
