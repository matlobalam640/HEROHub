@props(['disabled' => false])

<input
    {{ $disabled ? 'disabled' : '' }}
    {!! $attributes->merge(['class' => 'rounded-xl border-slate-200 bg-white/90 shadow-sm placeholder:text-slate-400 focus:border-hero-primary focus:ring-hero-primary']) !!}
>
