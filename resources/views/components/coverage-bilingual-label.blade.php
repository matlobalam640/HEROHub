@props(['key' => null, 'en' => null])

@php
    if ($key) {
        $en = \App\Support\CoverageFormTranslations::en($key);
    }
@endphp

<label {{ $attributes->merge(['class' => 'mb-1 block text-xs font-medium uppercase tracking-wide text-slate-700']) }}>
    {{ $en }}
</label>
