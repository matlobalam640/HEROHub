<button {{ $attributes->merge(['type' => 'submit', 'class' => 'hero-btn-primary']) }}>
    {{ $slot }}
</button>
