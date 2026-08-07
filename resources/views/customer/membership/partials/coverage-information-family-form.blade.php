@php
    $inputClass = 'w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:border-hero-primary focus:outline-none';
    $oldDependents = old('dependents');
    $dependentRows = is_array($oldDependents) && $oldDependents !== []
        ? $oldDependents
        : (count($dependents) > 0
            ? collect($dependents)->map(fn ($dep) => [
                'first_name' => $dep->first_name,
                'last_name' => $dep->last_name,
                'date_of_birth' => optional($dep->date_of_birth)?->format('Y-m-d'),
                'gender' => $dep->gender,
                'relationship' => $dep->relationship,
            ])->all()
            : [['first_name' => '', 'last_name' => '', 'date_of_birth' => '', 'gender' => '', 'relationship' => '']]);
    $selectedFlags = old('medical_condition_flags', $profile?->medical_condition_flags ?? []);
@endphp

<form method="POST" action="{{ route('customer.membership.coverage.update') }}" enctype="multipart/form-data" class="space-y-6" x-data="familyCoverageForm(@js($dependentRows))">
    @csrf
    @method('PUT')

    <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-md shadow-slate-200/50 ring-1 ring-slate-100">
        <details class="group border-b border-sky-100 bg-sky-50/80">
            <summary class="cursor-pointer list-none px-6 py-3 text-sm font-medium text-sky-950 marker:content-none [&::-webkit-details-marker]:hidden">
                <span class="inline-flex items-center gap-2">
                    <i class="fa-solid fa-circle-info text-sky-600" aria-hidden="true"></i>
                    Plan eligibility
                    <i class="fa-solid fa-chevron-down text-xs text-sky-700 transition group-open:rotate-180" aria-hidden="true"></i>
                </span>
            </summary>
            <p class="border-t border-sky-100 px-6 pb-3 pt-2 text-xs leading-relaxed text-sky-900">
                {{ \App\Support\CoverageFormTranslations::en('family_intro_short') }}
                {{ \App\Support\CoverageFormTranslations::en('family_intro_extended') }}
            </p>
        </details>
        <div class="hero-panel-header border-b border-slate-100 bg-gradient-to-r from-slate-50 to-[color:var(--dashboard-gold-soft)] px-6 py-4">
            <h2 class="text-sm font-semibold text-slate-900">{{ \App\Support\CoverageFormTranslations::en('primary_member') }}</h2>
            <p class="mt-1 text-xs text-slate-500">{{ \App\Support\CoverageFormTranslations::en('membership_number') }} {{ $membership->membership_number }}</p>
        </div>
        <div class="space-y-4 p-6">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <x-coverage-bilingual-label key="preferred_start_date" class="mb-1" />
                    <x-hero-date-input
                        name="preferred_coverage_start_date"
                        :value="old('preferred_coverage_start_date', optional($profile?->preferred_coverage_start_date)?->format('Y-m-d'))"
                        minDate="today"
                        :class="$inputClass"
                    />
                </div>
            </div>

            @include('customer.membership.partials.coverage-form-name-fields', ['prefix' => ''])
            @include('customer.membership.partials.coverage-form-contact-fields', ['prefix' => ''])
            @include('customer.membership.partials.coverage-form-address-fields', ['prefix' => ''])
        </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-md shadow-slate-200/50 ring-1 ring-slate-100">
        <div class="hero-panel-header border-b border-slate-100 bg-gradient-to-r from-slate-50 to-[color:var(--dashboard-gold-soft)] px-6 py-4">
            <h2 class="text-sm font-semibold text-slate-900">{{ \App\Support\CoverageFormTranslations::en('photo_id') }} &amp; {{ \App\Support\CoverageFormTranslations::en('passport') }}</h2>
            <p class="mt-1 text-xs text-slate-500">{{ \App\Support\CoverageFormTranslations::en('file_upload_help') }}</p>
        </div>
        <div class="grid grid-cols-1 gap-6 p-6 sm:grid-cols-2">
            <div>
                <x-coverage-bilingual-label key="photo_id" class="mb-2" />
                <input type="file" name="photo_id" accept=".jpg,.jpeg,.png,.pdf" @if(! $profile?->photo_id_path) required @endif class="{{ $inputClass }}">
                @if ($profile?->photo_id_path)
                    <p class="mt-2 text-xs text-green-700">{{ \App\Support\CoverageFormTranslations::en('file_on_file') }}</p>
                @endif
            </div>
            <div>
                <x-coverage-bilingual-label key="passport" class="mb-2" />
                <input type="file" name="passport" accept=".jpg,.jpeg,.png,.pdf" @if(! $profile?->passport_path) required @endif class="{{ $inputClass }}">
                @if ($profile?->passport_path)
                    <p class="mt-2 text-xs text-green-700">{{ \App\Support\CoverageFormTranslations::en('file_on_file') }}</p>
                @endif
            </div>
        </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-md shadow-slate-200/50 ring-1 ring-slate-100">
        <div class="hero-panel-header border-b border-slate-100 bg-gradient-to-r from-slate-50 to-[color:var(--dashboard-gold-soft)] px-6 py-4">
            <h2 class="text-sm font-semibold text-slate-900">{{ \App\Support\CoverageFormTranslations::en('emergency_contact') }}</h2>
        </div>
        <div class="grid grid-cols-1 gap-4 p-6 sm:grid-cols-3">
            <div>
                <x-coverage-bilingual-label key="first_name" class="mb-1" />
                <input type="text" name="emergency_contact_first_name" required
                       value="{{ old('emergency_contact_first_name', $profile?->emergency_contact_first_name) }}" class="{{ $inputClass }}">
            </div>
            <div>
                <x-coverage-bilingual-label key="last_name" class="mb-1" />
                <input type="text" name="emergency_contact_last_name" required
                       value="{{ old('emergency_contact_last_name', $profile?->emergency_contact_last_name) }}" class="{{ $inputClass }}">
            </div>
            <div>
                <x-coverage-bilingual-label key="phone" class="mb-1" />
                <input type="text" name="emergency_contact_phone" required
                       value="{{ old('emergency_contact_phone', $profile?->emergency_contact_phone) }}" class="{{ $inputClass }}">
            </div>
        </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-md shadow-slate-200/50 ring-1 ring-slate-100">
        <div class="hero-panel-header border-b border-slate-100 bg-gradient-to-r from-slate-50 to-[color:var(--dashboard-gold-soft)] px-6 py-4">
            <h2 class="text-sm font-semibold text-slate-900">{{ \App\Support\CoverageFormTranslations::en('dependents') }}</h2>
            <p class="mt-1 text-xs text-slate-500">{{ \App\Support\CoverageFormTranslations::en('dependents_help') }}</p>
        </div>
        <div class="space-y-4 p-6">
            <template x-for="(row, index) in dependents" :key="index">
                <div class="rounded-xl border border-slate-200 p-4">
                    <div class="mb-3 flex items-center justify-between gap-3">
                        <div class="text-sm font-semibold text-slate-900">
                            {{ \App\Support\CoverageFormTranslations::en('dependent_n') }}
                            <span x-text="index + 1"></span>
                        </div>
                        <button type="button" class="text-xs font-semibold text-red-700 hover:underline" x-show="dependents.length > 1" @click="removeDependent(index)">
                            {{ \App\Support\CoverageFormTranslations::en('remove') }}
                        </button>
                    </div>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
                        <div>
                            <x-coverage-bilingual-label key="first_name" class="mb-1" />
                            <input type="text" class="{{ $inputClass }}" x-model="row.first_name" :name="`dependents[${index}][first_name]`" required>
                        </div>
                        <div>
                            <x-coverage-bilingual-label key="last_name" class="mb-1" />
                            <input type="text" class="{{ $inputClass }}" x-model="row.last_name" :name="`dependents[${index}][last_name]`" required>
                        </div>
                        <div>
                            <x-coverage-bilingual-label key="date_of_birth" class="mb-1" />
                            <input type="text" class="{{ $inputClass }} hero-date-input" x-model="row.date_of_birth" :name="`dependents[${index}][date_of_birth]`" data-max-date="{{ now()->format('Y-m-d') }}" required autocomplete="off" placeholder="Select date">
                        </div>
                        <div>
                            <x-coverage-bilingual-label key="gender" class="mb-1" />
                            <select class="{{ $inputClass }}" x-model="row.gender" :name="`dependents[${index}][gender]`" required>
                                <option value="">{{ \App\Support\CoverageFormTranslations::en('select') }}</option>
                                @foreach ($genderOptionKeys as $value => $labelKey)
                                    <option value="{{ $value }}">{{ \App\Support\CoverageFormTranslations::en($labelKey) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-coverage-bilingual-label key="relationship" class="mb-1" />
                            <select class="{{ $inputClass }}" x-model="row.relationship" :name="`dependents[${index}][relationship]`" required>
                                <option value="">{{ \App\Support\CoverageFormTranslations::en('select') }}</option>
                                @foreach ($relationshipOptionKeys as $value => $labelKey)
                                    <option value="{{ $value }}">{{ \App\Support\CoverageFormTranslations::en($labelKey) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </template>
            <button type="button" @click="addDependent()" class="text-sm font-semibold text-hero-primary hover:underline">
                {{ \App\Support\CoverageFormTranslations::en('add_more') }}
            </button>
        </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-md shadow-slate-200/50 ring-1 ring-slate-100">
        <div class="hero-panel-header border-b border-slate-100 bg-gradient-to-r from-slate-50 to-[color:var(--dashboard-gold-soft)] px-6 py-4">
            <h2 class="text-sm font-semibold text-slate-900">{{ \App\Support\CoverageFormTranslations::en('notes') }}</h2>
        </div>
        <div class="p-6">
            <textarea name="notes" rows="4" class="{{ $inputClass }}" placeholder="{{ \App\Support\CoverageFormTranslations::en('notes_placeholder') }}">{{ old('notes', $profile?->notes) }}</textarea>
        </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-md shadow-slate-200/50 ring-1 ring-slate-100">
        <div class="hero-panel-header border-b border-slate-100 bg-gradient-to-r from-slate-50 to-[color:var(--dashboard-gold-soft)] px-6 py-4">
            <h2 class="text-sm font-semibold text-slate-900">{{ \App\Support\CoverageFormTranslations::en('insurance_section') }}</h2>
            <p class="mt-1 text-xs text-slate-500">{{ \App\Support\CoverageFormTranslations::en('insurance_section_help') }}</p>
        </div>
        <div class="space-y-4 p-6">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <x-coverage-bilingual-label key="insurance_company" class="mb-1" />
                    <input type="text" name="insurance_company" required value="{{ old('insurance_company', $profile?->insurance_company) }}" class="{{ $inputClass }}">
                </div>
                <div>
                    <x-coverage-bilingual-label key="policy_number" class="mb-1" />
                    <input type="text" name="insurance_policy_number" required value="{{ old('insurance_policy_number', $profile?->insurance_policy_number) }}" class="{{ $inputClass }}">
                </div>
            </div>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <x-coverage-bilingual-label key="policy_start" class="mb-1" />
                    <x-hero-date-input
                        name="insurance_effective_start"
                        :value="old('insurance_effective_start', optional($profile?->insurance_effective_start)?->format('Y-m-d'))"
                        :class="$inputClass"
                    />
                </div>
                <div>
                    <x-coverage-bilingual-label key="policy_end" class="mb-1" />
                    <x-hero-date-input
                        name="insurance_effective_end"
                        :value="old('insurance_effective_end', optional($profile?->insurance_effective_end)?->format('Y-m-d'))"
                        :class="$inputClass"
                    />
                </div>
            </div>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div>
                    <x-coverage-bilingual-label key="member_id" class="mb-1" />
                    <input type="text" name="insurance_member_id" value="{{ old('insurance_member_id', $profile?->insurance_member_id) }}" class="{{ $inputClass }}">
                </div>
                <div>
                    <x-coverage-bilingual-label key="policy_holder_name" class="mb-1" />
                    <input type="text" name="insurance_policy_holder_name" value="{{ old('insurance_policy_holder_name', $profile?->insurance_policy_holder_name) }}" class="{{ $inputClass }}">
                </div>
                <div>
                    <x-coverage-bilingual-label key="policy_holder_relationship" class="mb-1" />
                    <input type="text" name="insurance_policy_holder_relationship" value="{{ old('insurance_policy_holder_relationship', $profile?->insurance_policy_holder_relationship) }}" class="{{ $inputClass }}">
                </div>
            </div>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div>
                    <x-coverage-bilingual-label key="beneficiary_name" class="mb-1" />
                    <input type="text" name="insurance_beneficiary_name" value="{{ old('insurance_beneficiary_name', $profile?->insurance_beneficiary_name) }}" class="{{ $inputClass }}">
                </div>
                <div>
                    <x-coverage-bilingual-label key="beneficiary_relationship" class="mb-1" />
                    <input type="text" name="insurance_beneficiary_relationship" value="{{ old('insurance_beneficiary_relationship', $profile?->insurance_beneficiary_relationship) }}" class="{{ $inputClass }}">
                </div>
                <div>
                    <x-coverage-bilingual-label key="provider_phone" class="mb-1" />
                    <input type="text" name="insurance_provider_phone" required value="{{ old('insurance_provider_phone', $profile?->insurance_provider_phone) }}" class="{{ $inputClass }}">
                </div>
            </div>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div>
                    <x-coverage-bilingual-label key="plan_type" class="mb-1" />
                    <input type="text" name="insurance_plan_type" value="{{ old('insurance_plan_type', $profile?->insurance_plan_type) }}" class="{{ $inputClass }}">
                </div>
                <div>
                    <x-coverage-bilingual-label key="medevac_benefit" class="mb-1" />
                    <input type="text" name="medevac_max_benefit" value="{{ old('medevac_max_benefit', $profile?->medevac_max_benefit) }}" class="{{ $inputClass }}">
                </div>
                <div>
                    <x-coverage-bilingual-label key="medevac_policy" class="mb-1" />
                    <input type="text" name="medevac_policy_number" value="{{ old('medevac_policy_number', $profile?->medevac_policy_number) }}" class="{{ $inputClass }}">
                </div>
            </div>
        </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-md shadow-slate-200/50 ring-1 ring-slate-100">
        <div class="hero-panel-header border-b border-slate-100 bg-gradient-to-r from-slate-50 to-[color:var(--dashboard-gold-soft)] px-6 py-4">
            <h2 class="text-sm font-semibold text-slate-900">{{ \App\Support\CoverageFormTranslations::en('medical_section') }}</h2>
        </div>
        <div class="space-y-4 p-6">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <x-coverage-bilingual-label key="blood_type" class="mb-1" />
                    <select name="blood_type" required class="{{ $inputClass }}">
                        <option value="">{{ \App\Support\CoverageFormTranslations::en('select') }}</option>
                        @foreach ($bloodTypeOptions as $value => $label)
                            <option value="{{ $value }}" @selected(old('blood_type', $profile?->blood_type) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-coverage-bilingual-label key="allergies" class="mb-1" />
                    <input type="text" name="allergies" required value="{{ old('allergies', $profile?->allergies) }}" class="{{ $inputClass }}"
                           placeholder="{{ \App\Support\CoverageFormTranslations::en('allergies_placeholder') }}">
                </div>
            </div>
            <div>
                <x-coverage-bilingual-label key="chronic_conditions" class="mb-1" />
                <textarea name="chronic_conditions" rows="3" class="{{ $inputClass }}">{{ old('chronic_conditions', $profile?->chronic_conditions) }}</textarea>
            </div>
            <div>
                <p class="mb-2 text-sm font-semibold text-slate-900">{{ \App\Support\CoverageFormTranslations::en('medical_checklist') }}</p>
                <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                    @foreach ($medicalConditionFlags as $flag => $labels)
                        <label class="flex items-start gap-2 text-sm text-slate-700">
                            <input type="checkbox" name="medical_condition_flags[]" value="{{ $flag }}" @checked(in_array($flag, $selectedFlags, true)) class="mt-1">
                            <span>{{ $labels['en'] }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
            <div>
                <x-coverage-bilingual-label key="other_medical" class="mb-1" />
                <textarea name="other_medical_info" rows="3" class="{{ $inputClass }}">{{ old('other_medical_info', $profile?->other_medical_info) }}</textarea>
            </div>
        </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-md shadow-slate-200/50 ring-1 ring-slate-100">
        <div class="hero-panel-header border-b border-slate-100 bg-gradient-to-r from-slate-50 to-[color:var(--dashboard-gold-soft)] px-6 py-4">
            <h2 class="text-sm font-semibold text-slate-900">{{ \App\Support\CoverageFormTranslations::en('terms_section') }}</h2>
        </div>
        <div class="space-y-4 p-6">
            <div class="max-h-80 overflow-y-auto rounded-xl border border-slate-200 bg-slate-50 p-4">
                @include('customer.membership.partials.coverage-terms-content')
            </div>
            <label class="flex items-start gap-3 text-sm text-slate-700">
                <input type="checkbox" name="terms_accepted" value="1" required @checked(old('terms_accepted') || $profile?->terms_accepted_at) class="mt-1">
                <span>{{ \App\Support\CoverageFormTranslations::en('terms_accept') }}</span>
            </label>
        </div>
    </div>

    <div class="flex justify-center">
        <button type="submit" class="inline-flex min-w-[220px] items-center justify-center rounded-lg bg-orange-500 px-8 py-3 text-sm font-semibold text-white transition hover:bg-orange-600">
            {{ \App\Support\CoverageFormTranslations::en('submit') }}
        </button>
    </div>
</form>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('familyCoverageForm', (initialRows) => ({
            dependents: initialRows,
            addDependent() {
                this.dependents.push({
                    first_name: '',
                    last_name: '',
                    date_of_birth: '',
                    gender: '',
                    relationship: '',
                });
                this.$nextTick(() => window.initHeroDatePickers?.(this.$root));
            },
            removeDependent(index) {
                if (this.dependents.length > 1) {
                    this.dependents.splice(index, 1);
                }
            },
        }));
    });
</script>
@endpush
