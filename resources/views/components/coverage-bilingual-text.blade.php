@props(['en', 'fr' => null, 'key' => null])

@php
    if ($key) {
        $en = \App\Support\CoverageFormTranslations::en($key);
    }
@endphp

<p {{ $attributes->merge(['class' => 'text-sm text-slate-700']) }}>
    {{ $en }}
</p>
