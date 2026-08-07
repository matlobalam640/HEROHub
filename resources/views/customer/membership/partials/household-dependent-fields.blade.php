@php
    $inputClass = $inputClass ?? 'w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:border-hero-primary focus:outline-none';
    $namePrefix = $namePrefix ?? '';
    $values = $values ?? [];
    $fieldName = static function (string $field) use ($namePrefix): string {
        return $namePrefix !== '' ? "{$namePrefix}[{$field}]" : $field;
    };
    $fieldValue = static function (string $field) use ($values): mixed {
        return old($field, $values[$field] ?? null);
    };
@endphp

<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
    <div>
        <x-coverage-bilingual-label key="first_name" />
        <input type="text" name="{{ $fieldName('first_name') }}" required value="{{ $fieldValue('first_name') }}" class="{{ $inputClass }}">
    </div>
    <div>
        <x-coverage-bilingual-label key="last_name" />
        <input type="text" name="{{ $fieldName('last_name') }}" required value="{{ $fieldValue('last_name') }}" class="{{ $inputClass }}">
    </div>
    <div>
        <x-coverage-bilingual-label key="date_of_birth" />
        <x-hero-date-input
            :name="$fieldName('date_of_birth')"
            :value="$fieldValue('date_of_birth')"
            maxDate="{{ now()->format('Y-m-d') }}"
            required
            :class="$inputClass"
        />
    </div>
    <div>
        <x-coverage-bilingual-label key="gender" />
        <select name="{{ $fieldName('gender') }}" required class="{{ $inputClass }}">
            <option value="">{{ \App\Support\CoverageFormTranslations::t('select') }}</option>
            @foreach ($genderOptionKeys as $value => $labelKey)
                <option value="{{ $value }}" @selected($fieldValue('gender') === $value)>
                    {{ \App\Support\CoverageFormTranslations::t($labelKey) }}
                </option>
            @endforeach
        </select>
    </div>
    <div>
        <x-coverage-bilingual-label key="relationship" />
        <select name="{{ $fieldName('relationship') }}" required class="{{ $inputClass }}">
            <option value="">{{ \App\Support\CoverageFormTranslations::t('select') }}</option>
            @foreach ($relationshipOptionKeys as $value => $labelKey)
                <option value="{{ $value }}" @selected($fieldValue('relationship') === $value)>
                    {{ \App\Support\CoverageFormTranslations::t($labelKey) }}
                </option>
            @endforeach
        </select>
    </div>
</div>
