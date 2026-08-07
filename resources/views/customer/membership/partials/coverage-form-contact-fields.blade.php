<div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
    <div>
        <x-coverage-bilingual-label key="phone" class="mb-1" />
        <input type="text" name="phone" required value="{{ old('phone', $primary?->phone) }}" class="{{ $inputClass }}">
    </div>
    <div>
        <x-coverage-bilingual-label key="email" class="mb-1" />
        <input type="email" name="email" required value="{{ old('email', $primary?->email ?: auth()->user()->email) }}" class="{{ $inputClass }}">
    </div>
</div>
