<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center rounded-full border-2 border-hero-primary bg-white px-5 py-2.5 text-xs font-bold uppercase tracking-widest text-hero-primary shadow-sm transition hover:bg-hero-primary-soft focus:outline-none focus:ring-2 focus:ring-hero-primary/25 focus:ring-offset-2 disabled:opacity-25']) }}>
    {{ $slot }}
</button>
