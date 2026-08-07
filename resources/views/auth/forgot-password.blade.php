<x-guest-layout>
    <x-auth-session-status class="hero-alert hero-alert--success mb-6" :status="session('status')" />

    @include('auth.partials.form-header', [
        'badge' => 'Account recovery',
        'title' => __('Reset password'),
        'description' => __('Enter your email and we will send a secure link to choose a new password.'),
    ])

    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
        @csrf

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="mt-2 block w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="you@example.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3 pt-2">
            <a href="{{ route('login') }}" class="text-sm font-semibold text-[color:var(--hero-primary)] hover:text-[color:var(--hero-primary-hover)]">
                {{ __('Back to sign in') }}
            </a>
            <x-primary-button class="guest-auth-submit px-6 py-3">
                {{ __('Email reset link') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
