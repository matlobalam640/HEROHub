@props(['disabled' => false])

<input
    {{ $disabled ? 'disabled' : '' }}
    {!! $attributes->merge(['class' => 'block w-full rounded-xl border-slate-200/90 bg-white/90 px-4 py-2.5 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 backdrop-blur-sm transition focus:border-hero-primary focus:ring-2 focus:ring-hero-primary/20 disabled:cursor-not-allowed disabled:bg-slate-50 disabled:text-slate-500']) !!}
>
