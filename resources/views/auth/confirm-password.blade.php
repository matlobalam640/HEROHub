<x-guest-layout>
    @include('auth.partials.form-header', [
        'badge' => 'Security check',
        'title' => __('Confirm password'),
        'description' => __('This is a secure area. Please confirm your password before continuing.'),
    ])

    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-5">
        @csrf

        <div>
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="mt-2 block w-full" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex justify-end pt-2">
            <x-primary-button class="guest-auth-submit px-6 py-3">
                {{ __('Confirm') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
