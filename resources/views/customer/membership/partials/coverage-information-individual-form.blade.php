@php($inputClass = $inputClass ?? 'w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:border-hero-primary focus:outline-none')

<div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    <div class="lg:col-span-2 overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-md shadow-slate-200/50 ring-1 ring-slate-100">
        <div class="hero-panel-header border-b border-slate-100 px-6 py-4">
            <h2 class="text-sm font-semibold text-slate-900">{{ \App\Support\CoverageFormTranslations::t('primary_member') }}</h2>
            <p class="mt-1 text-xs text-slate-500">{{ \App\Support\CoverageFormTranslations::t('membership_number') }} {{ $membership->membership_number }}</p>
        </div>
        <div class="p-6 text-slate-900">
            <form method="POST" action="{{ route('customer.membership.coverage.update') }}" class="space-y-4">
                @csrf
                @method('PUT')

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
                            <option value="">{{ \App\Support\CoverageFormTranslations::t('select') }}</option>
                            @foreach ($genderOptionKeys as $value => $labelKey)
                                <option value="{{ $value }}" @selected(old('gender', $primary?->gender) === $value)>
                                    {{ \App\Support\CoverageFormTranslations::t($labelKey) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <x-coverage-bilingual-label key="phone" />
                        <input type="text" name="phone" required value="{{ old('phone', $primary?->phone) }}" class="{{ $inputClass }}">
                    </div>
                    <div>
                        <x-coverage-bilingual-label key="id_number" />
                        <input type="text" name="id_number" required value="{{ old('id_number', $primary?->id_number) }}" class="{{ $inputClass }}">
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <x-coverage-bilingual-label key="country" />
                        <input type="text" name="country" required value="{{ old('country', $primary?->country ?: 'USA') }}" class="{{ $inputClass }}">
                    </div>
                    <div>
                        <x-coverage-bilingual-label key="city" />
                        <input type="text" name="city" required value="{{ old('city', $primary?->city) }}" class="{{ $inputClass }}">
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" class="inline-flex rounded-lg bg-hero-primary px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-hero-primary-hover">
                        {{ \App\Support\CoverageFormTranslations::t('save_continue') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="space-y-6">
        <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-md shadow-slate-200/50 ring-1 ring-slate-100">
            <div class="hero-panel-header border-b border-slate-100 px-6 py-4">
                <h2 class="text-sm font-semibold text-slate-900">{{ \App\Support\CoverageFormTranslations::t('coverage_type') }}</h2>
            </div>
            <div class="p-6 text-sm text-slate-700">
                <p class="font-medium text-slate-900">{{ $membership->plan?->name ?? '—' }}</p>
            </div>
        </div>

        @if ($missingFields !== [])
            <div class="rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-950">
                <p class="font-semibold">{{ \App\Support\CoverageFormTranslations::t('reminder') }}</p>
                <p class="mt-1">{{ \App\Support\CoverageFormTranslations::t('reminder_body') }}</p>
            </div>
        @else
            <div class="rounded-2xl border border-green-200 bg-green-50 px-5 py-4 text-sm text-green-900">
                <p class="font-semibold">{{ \App\Support\CoverageFormTranslations::t('profile_complete') }}</p>
                <p class="mt-1">{{ \App\Support\CoverageFormTranslations::t('profile_complete_body') }}</p>
            </div>
        @endif
    </div>
</div>
