@php
    $inputClass = 'coverage-vip-input';
    $selectedFlags = old('medical_condition_flags', $profile?->medical_condition_flags ?? []);
    $savedQuestionnaire = old('health_questionnaire', $profile?->health_questionnaire ?? []);
@endphp

<div class="coverage-vip-shell coverage-individual-plan-shell">
    <aside class="coverage-vip-hero" aria-hidden="true">
        <img
            src="{{ asset('images/banner-image.avif') }}"
            alt=""
            class="coverage-vip-hero-image"
            loading="lazy"
        >
    </aside>

    <div class="coverage-vip-form-column">
        <header class="coverage-vip-title-bar coverage-individual-plan-header">
            <h1 class="coverage-vip-title">{{ $formTitle }}</h1>
            <p class="coverage-individual-plan-intro">{{ \App\Support\CoverageFormTranslations::en('individual_plan_intro') }}</p>
        </header>

        <form method="POST" action="{{ route('customer.membership.coverage.update') }}" class="coverage-vip-form">
            @csrf
            @method('PUT')

            <section class="coverage-vip-section">
                <h2 class="coverage-vip-section-title">{{ \App\Support\CoverageFormTranslations::en('personal_information') }}</h2>
                <div class="coverage-vip-grid coverage-vip-grid-1">
                    @include('customer.membership.partials.coverage-form-name-fields', ['prefix' => ''])
                    <div class="coverage-vip-grid coverage-vip-grid-2">
                        <div>
                            <x-coverage-bilingual-label key="phone" class="mb-1" />
                            <input type="text" name="phone" required value="{{ old('phone', $primary?->phone) }}" class="{{ $inputClass }}" placeholder="000 0000 000">
                        </div>
                        <div>
                            <x-coverage-bilingual-label key="email" class="mb-1" />
                            <input type="email" name="email" required value="{{ old('email', $primary?->email) }}" class="{{ $inputClass }}">
                        </div>
                    </div>
                    <div>
                        <x-coverage-bilingual-label key="street" class="mb-1" />
                        <input type="text" name="street" required value="{{ old('street', $primary?->street) }}" class="{{ $inputClass }}">
                    </div>
                    <div class="coverage-vip-grid coverage-vip-grid-2">
                        <div>
                            <x-coverage-bilingual-label key="city" class="mb-1" />
                            <input type="text" name="city" required value="{{ old('city', $primary?->city) }}" class="{{ $inputClass }}">
                        </div>
                        <div>
                            <x-coverage-bilingual-label key="country" class="mb-1" />
                            <input type="text" name="country" required value="{{ old('country', $primary?->country ?: 'USA') }}" class="{{ $inputClass }}">
                        </div>
                    </div>
                    <div>
                        <x-coverage-bilingual-label key="id_number" class="mb-1" />
                        <input type="text" name="id_number" required value="{{ old('id_number', $primary?->id_number) }}" class="{{ $inputClass }}">
                    </div>
                    <div>
                        <x-coverage-bilingual-label key="primary_care_provider" class="mb-1" />
                        <p class="coverage-vip-help">{{ \App\Support\CoverageFormTranslations::en('primary_care_provider_prompt') }}</p>
                        <textarea name="primary_care_provider" rows="3" required class="{{ $inputClass }}">{{ old('primary_care_provider', $profile?->primary_care_provider) }}</textarea>
                    </div>
                </div>
            </section>

            <section class="coverage-vip-section coverage-individual-emergency-section">
                <h2 class="coverage-vip-section-title">{{ \App\Support\CoverageFormTranslations::en('emergency_contact') }}</h2>
                <div class="coverage-vip-grid coverage-vip-grid-2">
                    <div>
                        <x-coverage-bilingual-label key="first_name" class="mb-1" />
                        <input type="text" name="emergency_contact_first_name" required value="{{ old('emergency_contact_first_name', $profile?->emergency_contact_first_name) }}" class="{{ $inputClass }}">
                    </div>
                    <div>
                        <x-coverage-bilingual-label key="last_name" class="mb-1" />
                        <input type="text" name="emergency_contact_last_name" required value="{{ old('emergency_contact_last_name', $profile?->emergency_contact_last_name) }}" class="{{ $inputClass }}">
                    </div>
                    <div>
                        <x-coverage-bilingual-label key="relationship" class="mb-1" />
                        <input type="text" name="emergency_contact_relationship" required value="{{ old('emergency_contact_relationship', $profile?->emergency_contact_relationship) }}" class="{{ $inputClass }}">
                    </div>
                    <div>
                        <x-coverage-bilingual-label key="phone" class="mb-1" />
                        <input type="text" name="emergency_contact_phone" required value="{{ old('emergency_contact_phone', $profile?->emergency_contact_phone) }}" class="{{ $inputClass }}">
                    </div>
                    <div>
                        <x-coverage-bilingual-label key="emergency_contact_gender" class="mb-1" />
                        <div class="coverage-vip-question-options">
                            <label class="coverage-vip-radio">
                                <input type="radio" name="emergency_contact_gender" value="male" @checked(old('emergency_contact_gender', $profile?->emergency_contact_gender) === 'male')>
                                <span>{{ \App\Support\CoverageFormTranslations::en('gender_male') }}</span>
                            </label>
                            <label class="coverage-vip-radio">
                                <input type="radio" name="emergency_contact_gender" value="female" @checked(old('emergency_contact_gender', $profile?->emergency_contact_gender) === 'female')>
                                <span>{{ \App\Support\CoverageFormTranslations::en('gender_female') }}</span>
                            </label>
                        </div>
                    </div>
                </div>
            </section>

            <section class="coverage-vip-section">
                <h2 class="coverage-vip-section-title">{{ \App\Support\CoverageFormTranslations::en('insurance_section') }}</h2>
                <div class="coverage-vip-grid coverage-vip-grid-2">
                    <div>
                        <x-coverage-bilingual-label key="health_plan_provider" class="mb-1" />
                        <input type="text" name="health_plan_provider" required value="{{ old('health_plan_provider', $profile?->health_plan_provider) }}" class="{{ $inputClass }}" placeholder="{{ \App\Support\CoverageFormTranslations::en('select') }}">
                    </div>
                    <div>
                        <x-coverage-bilingual-label key="health_insurer" class="mb-1" />
                        <input type="text" name="health_insurer" required value="{{ old('health_insurer', $profile?->health_insurer) }}" class="{{ $inputClass }}" placeholder="{{ \App\Support\CoverageFormTranslations::en('select') }}">
                    </div>
                </div>
                <p class="coverage-vip-help coverage-individual-insurance-note">{{ \App\Support\CoverageFormTranslations::en('insurance_record_notice') }}</p>
            </section>

            @include('customer.membership.partials.coverage-individual-health-questionnaire', [
                'inputClass' => $inputClass,
                'savedQuestionnaire' => $savedQuestionnaire,
            ])

            <section class="coverage-vip-section">
                <h2 class="coverage-vip-section-title">{{ \App\Support\CoverageFormTranslations::en('medical_section') }}</h2>
                <div class="coverage-vip-grid coverage-vip-grid-1">
                    <div>
                        <p class="coverage-vip-checklist-title">{{ \App\Support\CoverageFormTranslations::en('medical_checklist') }}</p>
                        <div class="coverage-vip-checklist">
                            @foreach ($individualMedicalConditions as $flag => $labels)
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
                    <div class="coverage-vip-grid coverage-vip-grid-2">
                        <div>
                            <x-coverage-bilingual-label key="allergies" class="mb-1" />
                            <input type="text" name="allergies" value="{{ old('allergies', $profile?->allergies) }}" class="{{ $inputClass }}"
                                   placeholder="{{ \App\Support\CoverageFormTranslations::en('allergies_placeholder') }}">
                        </div>
                        <div>
                            <x-coverage-bilingual-label key="chronic_conditions" class="mb-1" />
                            <textarea name="chronic_conditions" rows="2" class="{{ $inputClass }}">{{ old('chronic_conditions', $profile?->chronic_conditions) }}</textarea>
                        </div>
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
