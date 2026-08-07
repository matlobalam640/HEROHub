<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center rounded-2xl border border-transparent bg-hero-primary px-5 py-2.5 text-sm font-semibold text-white shadow-hero-cta transition hover:bg-hero-primary-hover active:bg-hero-primary-pressed focus:outline-none focus:ring-2 focus:ring-hero-primary/35 focus:ring-offset-2']) }}>
    {{ $slot }}
</button>
