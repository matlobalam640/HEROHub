<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center rounded-full border border-transparent bg-red-600 px-5 py-2 text-xs font-bold uppercase tracking-widest text-white shadow-sm transition hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 active:bg-red-800']) }}>
    {{ $slot }}
</button>
