<x-guest-layout>
    @include('auth.partials.form-header', [
        'badge' => 'Registration disabled',
        'title' => __('Create account'),
        'description' => __('New memberships are purchased on the HERO public website. This portal is for existing members only.'),
    ])

    <div class="hero-empty-state mb-6">
        <div class="hero-empty-state__icon"><i class="fa-solid fa-user-plus" aria-hidden="true"></i></div>
        <p class="text-sm text-slate-600">Registration in the portal is disabled. You will be redirected to subscribe on heroclientrescue.com.</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-5">
        @csrf

        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" class="mt-2 block w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="mt-2 block w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="mt-2 block w-full" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
            <x-text-input id="password_confirmation" class="mt-2 block w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3 pt-2">
            <a class="text-sm font-semibold text-[color:var(--hero-primary)] hover:text-[color:var(--hero-primary-hover)]" href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>
            <x-primary-button class="guest-auth-submit px-6 py-3">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
