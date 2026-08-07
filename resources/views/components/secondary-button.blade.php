<button {{ $attributes->merge(['type' => 'button', 'class' => 'hero-btn-secondary disabled:cursor-not-allowed disabled:opacity-40']) }}>
    {{ $slot }}
</button>
