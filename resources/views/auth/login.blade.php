<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-6" :status="session('status')" />

    <div class="mb-7">
        <div class="inline-flex items-center gap-2 rounded-full bg-slate-50 px-3 py-1 text-xs font-semibold text-slate-600">
            <span class="inline-flex h-2 w-2 rounded-full bg-hero-primary"></span>
            Secure portal access
        </div>
        <h1 class="mt-3 text-3xl font-semibold tracking-tight font-display">
            Sign in
        </h1>
        <p class="mt-2 text-sm text-slate-600 font-['Open_Sans'] leading-relaxed">
            Log in to manage your membership, billing, and coverage details.
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
                    <a class="text-xs font-semibold text-hero-primary hover:text-hero-primary-pressed"
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
                <input id="remember_me" type="checkbox" class="rounded-lg border-slate-300 text-hero-primary shadow-sm focus:ring-hero-primary" name="remember">
                <span class="ms-2 text-sm text-slate-600">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="mt-7">
            <x-primary-button class="w-full py-3 text-sm normal-case tracking-normal rounded-2xl">
                {{ __('Log in') }}
            </x-primary-button>

            <div class="mt-5 text-center text-xs text-slate-500 font-['Open_Sans']">
                Don’t have an account?
                <a class="font-semibold text-hero-primary hover:text-hero-primary-pressed" href="{{ route('register') }}">
                    Create one
                </a>
            </div>
        </div>
    </form>
</x-guest-layout>
