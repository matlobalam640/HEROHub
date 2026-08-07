<div>
    <x-coverage-bilingual-label key="street" class="mb-1" />
    <input type="text" name="street" required value="{{ old('street', $primary?->street) }}" class="{{ $inputClass }}">
</div>
<div>
    <x-coverage-bilingual-label key="street_line2" class="mb-1" />
    <input type="text" name="street_line2" value="{{ old('street_line2', $primary?->street_line2) }}" class="{{ $inputClass }}">
</div>
<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
    <div>
        <x-coverage-bilingual-label key="city" class="mb-1" />
        <input type="text" name="city" required value="{{ old('city', $primary?->city) }}" class="{{ $inputClass }}">
    </div>
    <div>
        <x-coverage-bilingual-label key="state" class="mb-1" />
        <input type="text" name="state" required value="{{ old('state', $primary?->state) }}" class="{{ $inputClass }}">
    </div>
    <div>
        <x-coverage-bilingual-label key="zip_code" class="mb-1" />
        <input type="text" name="zip_code" required value="{{ old('zip_code', $primary?->zip_code) }}" class="{{ $inputClass }}">
    </div>
    <div>
        <x-coverage-bilingual-label key="country" class="mb-1" />
        <input type="text" name="country" required value="{{ old('country', $primary?->country ?: 'USA') }}" class="{{ $inputClass }}">
    </div>
</div>
