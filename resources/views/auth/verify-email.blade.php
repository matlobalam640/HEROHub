<x-guest-layout>
    @include('auth.partials.form-header', [
        'badge' => 'Email verification',
        'title' => __('Verify your email'),
        'description' => __('Before getting started, please verify your email address using the link we sent you.'),
    ])

    @if (session('status') == 'verification-link-sent')
        <div class="hero-alert hero-alert--success mb-6">
            {{ __('A new verification link has been sent to the email address you provided during registration.') }}
        </div>
    @endif

    <div class="flex flex-wrap items-center justify-between gap-4">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <x-primary-button class="guest-auth-submit px-6 py-3">
                {{ __('Resend verification email') }}
            </x-primary-button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="text-sm font-semibold text-slate-600 transition hover:text-[color:var(--hero-primary)]">
                {{ __('Log Out') }}
            </button>
        </form>
    </div>
</x-guest-layout>
