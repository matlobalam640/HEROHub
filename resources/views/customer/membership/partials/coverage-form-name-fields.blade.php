<div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
    <div>
        <x-coverage-bilingual-label key="first_name" />
        <input type="text" name="first_name" required value="{{ old('first_name', $primary?->first_name) }}" class="{{ $inputClass }}">
    </div>
    <div>
        <x-coverage-bilingual-label key="last_name" />
        <input type="text" name="last_name" required value="{{ old('last_name', $primary?->last_name) }}" class="{{ $inputClass }}">
    </div>
</div>
<div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
    <div>
        <x-coverage-bilingual-label key="date_of_birth" />
        <x-hero-date-input
            name="date_of_birth"
            :value="old('date_of_birth', optional($primary?->date_of_birth)?->format('Y-m-d'))"
            maxDate="{{ now()->format('Y-m-d') }}"
            required
            :class="$inputClass"
        />
    </div>
    <div>
        <x-coverage-bilingual-label key="gender" />
        <select name="gender" required class="{{ $inputClass }}">
            <option value="">{{ \App\Support\CoverageFormTranslations::en('select') }}</option>
            @foreach ($genderOptionKeys as $value => $labelKey)
                <option value="{{ $value }}" @selected(old('gender', $primary?->gender) === $value)>
                    {{ \App\Support\CoverageFormTranslations::en($labelKey) }}
                </option>
            @endforeach
        </select>
    </div>
</div>
