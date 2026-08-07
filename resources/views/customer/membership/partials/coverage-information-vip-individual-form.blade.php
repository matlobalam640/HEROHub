@php
    $inputClass = 'coverage-vip-input';
    $selectedFlags = old('medical_condition_flags', $profile?->medical_condition_flags ?? []);
    $savedQuestionnaire = old('health_questionnaire', $profile?->health_questionnaire ?? []);
    $measurementUnit = old('measurement_unit', $profile?->measurement_unit ?? 'metric');
@endphp

<div class="coverage-vip-shell">
    <aside class="coverage-vip-hero" aria-hidden="true">
        <img
            src="{{ asset('images/banner-image.avif') }}"
            alt=""
            class="coverage-vip-hero-image"
            loading="lazy"
        >
    </aside>

    <div class="coverage-vip-form-column">
        <header class="coverage-vip-title-bar">
            <h1 class="coverage-vip-title">{{ $formTitle }}</h1>
            <p class="coverage-vip-subtitle">{{ \App\Support\CoverageFormTranslations::en('membership_number') }} {{ $membership->membership_number }}</p>
        </header>

        <form method="POST" action="{{ route('customer.membership.coverage.update') }}" enctype="multipart/form-data" class="coverage-vip-form">
            @csrf
            @method('PUT')

            <section class="coverage-vip-section">
                <h2 class="coverage-vip-section-title">{{ \App\Support\CoverageFormTranslations::en('personal_information') }}</h2>
                <div class="coverage-vip-grid coverage-vip-grid-2">
                    @include('customer.membership.partials.coverage-form-name-fields', ['prefix' => ''])
                    <div>
                        <x-coverage-bilingual-label key="phone" class="mb-1" />
                        <input type="text" name="phone" required value="{{ old('phone', $primary?->phone) }}" class="{{ $inputClass }}">
                    </div>
                    <div>
                        <x-coverage-bilingual-label key="email" class="mb-1" />
                        <input type="email" name="email" required value="{{ old('email', $primary?->email) }}" class="{{ $inputClass }}">
                    </div>
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
            </section>

            <section class="coverage-vip-section">
                <h2 class="coverage-vip-section-title">{{ \App\Support\CoverageFormTranslations::en('identification') }}</h2>
                <div class="coverage-vip-grid coverage-vip-grid-2">
                    <div>
                        <x-coverage-bilingual-label key="nationality" class="mb-1" />
                        <input type="text" name="nationality" required value="{{ old('nationality', $primary?->nationality) }}" class="{{ $inputClass }}">
                    </div>
                    <div>
                        <x-coverage-bilingual-label key="resident_status" class="mb-1" />
                        <select name="resident_status" required class="{{ $inputClass }}">
                            <option value="">{{ \App\Support\CoverageFormTranslations::en('select') }}</option>
                            @foreach ($residentStatusOptions as $value => $label)
                                <option value="{{ $value }}" @selected(old('resident_status', $profile?->resident_status) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-coverage-bilingual-label key="id_number" class="mb-1" />
                        <input type="text" name="id_number" required value="{{ old('id_number', $primary?->id_number) }}" class="{{ $inputClass }}">
                    </div>
                    <div>
                        <x-coverage-bilingual-label key="passport_expiry_date" class="mb-1" />
                        <x-hero-date-input
                            name="passport_expiry_date"
                            :value="old('passport_expiry_date', optional($primary?->passport_expiry_date)?->format('Y-m-d'))"
                            minDate="today"
                            required
                            :class="$inputClass"
                        />
                    </div>
                </div>
            </section>

            <section class="coverage-vip-section">
                <h2 class="coverage-vip-section-title">{{ \App\Support\CoverageFormTranslations::en('physical_metrics') }}</h2>
                <div class="coverage-vip-unit-toggle">
                    <label class="coverage-vip-radio">
                        <input type="radio" name="measurement_unit" value="metric" @checked($measurementUnit === 'metric') required>
                        <span>{{ \App\Support\CoverageFormTranslations::en('unit_metric') }}</span>
                    </label>
                    <label class="coverage-vip-radio">
                        <input type="radio" name="measurement_unit" value="imperial" @checked($measurementUnit === 'imperial') required>
                        <span>{{ \App\Support\CoverageFormTranslations::en('unit_imperial') }}</span>
                    </label>
                </div>
                <div class="coverage-vip-grid coverage-vip-grid-2">
                    <div>
                        <x-coverage-bilingual-label key="height" class="mb-1" />
                        <input type="text" name="height" required value="{{ old('height', $profile?->height) }}" class="{{ $inputClass }}">
                    </div>
                    <div>
                        <x-coverage-bilingual-label key="weight" class="mb-1" />
                        <input type="text" name="weight" required value="{{ old('weight', $profile?->weight) }}" class="{{ $inputClass }}">
                    </div>
                    <div class="coverage-vip-grid-span-2">
                        <x-coverage-bilingual-label key="occupation" class="mb-1" />
                        <input type="text" name="occupation" required value="{{ old('occupation', $profile?->occupation) }}" class="{{ $inputClass }}">
                    </div>
                </div>
            </section>

            <section class="coverage-vip-section">
                <h2 class="coverage-vip-section-title">{{ \App\Support\CoverageFormTranslations::en('full_address') }}</h2>
                <div class="coverage-vip-grid coverage-vip-grid-1">
                    @include('customer.membership.partials.coverage-form-address-fields', ['prefix' => ''])
                </div>
            </section>

            <section class="coverage-vip-section">
                <h2 class="coverage-vip-section-title">{{ \App\Support\CoverageFormTranslations::en('photo_id') }} &amp; {{ \App\Support\CoverageFormTranslations::en('passport') }}</h2>
                <p class="coverage-vip-help">{{ \App\Support\CoverageFormTranslations::en('file_upload_help') }}</p>
                <div class="coverage-vip-grid coverage-vip-grid-2">
                    <div>
                        <x-coverage-bilingual-label key="photo_id" class="mb-1" />
                        <input type="file" name="photo_id" accept=".jpg,.jpeg,.png,.pdf" @if(! $profile?->photo_id_path) required @endif class="{{ $inputClass }}">
                        @if ($profile?->photo_id_path)
                            <p class="coverage-vip-file-note">{{ \App\Support\CoverageFormTranslations::en('file_on_file') }}</p>
                        @endif
                    </div>
                    <div>
                        <x-coverage-bilingual-label key="passport" class="mb-1" />
                        <input type="file" name="passport" accept=".jpg,.jpeg,.png,.pdf" @if(! $profile?->passport_path) required @endif class="{{ $inputClass }}">
                        @if ($profile?->passport_path)
                            <p class="coverage-vip-file-note">{{ \App\Support\CoverageFormTranslations::en('file_on_file') }}</p>
                        @endif
                    </div>
                </div>
            </section>

            <section class="coverage-vip-section">
                <h2 class="coverage-vip-section-title">{{ \App\Support\CoverageFormTranslations::en('emergency_contact') }}</h2>
                <div class="coverage-vip-grid coverage-vip-grid-3">
                    <div>
                        <x-coverage-bilingual-label key="first_name" class="mb-1" />
                        <input type="text" name="emergency_contact_first_name" required value="{{ old('emergency_contact_first_name', $profile?->emergency_contact_first_name) }}" class="{{ $inputClass }}">
                    </div>
                    <div>
                        <x-coverage-bilingual-label key="last_name" class="mb-1" />
                        <input type="text" name="emergency_contact_last_name" required value="{{ old('emergency_contact_last_name', $profile?->emergency_contact_last_name) }}" class="{{ $inputClass }}">
                    </div>
                    <div>
                        <x-coverage-bilingual-label key="phone" class="mb-1" />
                        <input type="text" name="emergency_contact_phone" required value="{{ old('emergency_contact_phone', $profile?->emergency_contact_phone) }}" class="{{ $inputClass }}">
                    </div>
                </div>
            </section>

            <section class="coverage-vip-section">
                <h2 class="coverage-vip-section-title">{{ \App\Support\CoverageFormTranslations::en('insurance_section') }}</h2>
                <p class="coverage-vip-help">{{ \App\Support\CoverageFormTranslations::en('insurance_section_help') }}</p>
                <div class="coverage-vip-grid coverage-vip-grid-2">
                    <div>
                        <x-coverage-bilingual-label key="health_plan_name" class="mb-1" />
                        <input type="text" name="insurance_company" required value="{{ old('insurance_company', $profile?->insurance_company) }}" class="{{ $inputClass }}">
                    </div>
                    <div>
                        <x-coverage-bilingual-label key="health_plan_level" class="mb-1" />
                        <input type="text" name="insurance_plan_type" value="{{ old('insurance_plan_type', $profile?->insurance_plan_type) }}" class="{{ $inputClass }}">
                    </div>
                    <div>
                        <x-coverage-bilingual-label key="policy_number" class="mb-1" />
                        <input type="text" name="insurance_policy_number" required value="{{ old('insurance_policy_number', $profile?->insurance_policy_number) }}" class="{{ $inputClass }}">
                    </div>
                    <div>
                        <x-coverage-bilingual-label key="provider_phone" class="mb-1" />
                        <input type="text" name="insurance_provider_phone" required value="{{ old('insurance_provider_phone', $profile?->insurance_provider_phone) }}" class="{{ $inputClass }}">
                    </div>
                    <div>
                        <x-coverage-bilingual-label key="policy_start" class="mb-1" />
                        <x-hero-date-input name="insurance_effective_start" :value="old('insurance_effective_start', optional($profile?->insurance_effective_start)?->format('Y-m-d'))" :class="$inputClass" />
                    </div>
                    <div>
                        <x-coverage-bilingual-label key="policy_end" class="mb-1" />
                        <x-hero-date-input name="insurance_effective_end" :value="old('insurance_effective_end', optional($profile?->insurance_effective_end)?->format('Y-m-d'))" :class="$inputClass" />
                    </div>
                    <div>
                        <x-coverage-bilingual-label key="member_id" class="mb-1" />
                        <input type="text" name="insurance_member_id" value="{{ old('insurance_member_id', $profile?->insurance_member_id) }}" class="{{ $inputClass }}">
                    </div>
                    <div>
                        <x-coverage-bilingual-label key="medevac_benefit" class="mb-1" />
                        <input type="text" name="medevac_max_benefit" value="{{ old('medevac_max_benefit', $profile?->medevac_max_benefit) }}" class="{{ $inputClass }}">
                    </div>
                </div>
            </section>

            @include('customer.membership.partials.coverage-health-questionnaire', [
                'inputClass' => $inputClass,
                'savedQuestionnaire' => $savedQuestionnaire,
            ])

            <section class="coverage-vip-section">
                <h2 class="coverage-vip-section-title">{{ \App\Support\CoverageFormTranslations::en('medical_section') }}</h2>
                <div class="coverage-vip-grid coverage-vip-grid-2">
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
                <div class="coverage-vip-grid coverage-vip-grid-1 mt-4">
                    <div>
                        <x-coverage-bilingual-label key="chronic_conditions" class="mb-1" />
                        <textarea name="chronic_conditions" rows="3" class="{{ $inputClass }}">{{ old('chronic_conditions', $profile?->chronic_conditions) }}</textarea>
                    </div>
                    <div>
                        <p class="coverage-vip-checklist-title">{{ \App\Support\CoverageFormTranslations::en('medical_checklist') }}</p>
                        <div class="coverage-vip-checklist">
                            @foreach ($medicalConditionFlags as $flag => $labels)
                                <label class="coverage-vip-check">
                                    <input type="checkbox" name="medical_condition_flags[]" value="{{ $flag }}" @checked(in_array($flag, $selectedFlags, true))>
                                    <span>{{ $labels['en'] }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div>
                        <x-coverage-bilingual-label key="other_medical" class="mb-1" />
                        <textarea name="other_medical_info" rows="4" class="{{ $inputClass }}" placeholder="{{ \App\Support\CoverageFormTranslations::en('notes_placeholder') }}">{{ old('other_medical_info', $profile?->other_medical_info) }}</textarea>
                    </div>
                </div>
            </section>

            <section class="coverage-vip-section">
                <h2 class="coverage-vip-section-title">{{ \App\Support\CoverageFormTranslations::en('terms_section') }}</h2>
                <div class="coverage-vip-terms-box">
                    @include('customer.membership.partials.coverage-terms-content')
                </div>
                <label class="coverage-vip-check coverage-vip-check-block">
                    <input type="checkbox" name="terms_accepted" value="1" required @checked(old('terms_accepted') || $profile?->terms_accepted_at)>
                    <span>{{ \App\Support\CoverageFormTranslations::en('terms_accept') }}</span>
                </label>
            </section>

            <div class="coverage-vip-submit-wrap">
                <button type="submit" class="coverage-vip-submit">
                    {{ \App\Support\CoverageFormTranslations::en('submit') }}
                </button>
            </div>
        </form>
    </div>
</div>
