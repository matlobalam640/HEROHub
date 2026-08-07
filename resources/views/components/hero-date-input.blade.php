@props([
    'name',
    'value' => '',
    'required' => false,
    'minDate' => null,
    'maxDate' => null,
    'placeholder' => 'Select date',
])

<input
    type="text"
    name="{{ $name }}"
    value="{{ $value }}"
    @if($required) required @endif
    autocomplete="off"
    placeholder="{{ $placeholder }}"
    @if($minDate) data-min-date="{{ $minDate }}" @endif
    @if($maxDate) data-max-date="{{ $maxDate }}" @endif
    {{ $attributes->merge(['class' => 'hero-date-input']) }}
/>
