@php
    $inputClass = 'coverage-vip10-input';
    $oldTrips = old('trips');
    $savedTrips = $profile?->trip_details['trips'] ?? [];
    $tripRows = is_array($oldTrips) && $oldTrips !== []
        ? $oldTrips
        : (is_array($savedTrips) && $savedTrips !== []
            ? $savedTrips
            : [['from' => '', 'price' => '', 'date' => '']]);
    $selectedFlags = old('medical_condition_flags', $profile?->medical_condition_flags ?? []);
    $selectedPreferences = old('travel_preferences', $profile?->travel_preferences ?? []);
@endphp

<div class="coverage-vip10-shell">
    <header class="coverage-vip10-hero">
        <div class="coverage-vip10-hero-overlay">
            <div class="coverage-vip10-hero-inner">
                <img src="{{ asset('images/hero-logo-white.svg') }}" alt="HERO" class="coverage-vip10-logo" onerror="this.style.display='none'">
                <h1 class="coverage-vip10-hero-title">{{ $formTitle }}</h1>
                <p class="coverage-vip10-hero-copy">{{ \App\Support\CoverageFormTranslations::t('vip10_intro') }}</p>
            </div>
        </div>
    </header>

    <div class="coverage-vip10-body">
        <form
            method="POST"
            action="{{ route('customer.membership.coverage.update') }}"
            class="coverage-vip10-form"
            x-data="vip10DayCoverageForm(@js($tripRows))"
        >
            @csrf
            @method('PUT')

            <section class="coverage-vip10-section">
                <h2 class="coverage-vip10-section-title">{{ \App\Support\CoverageFormTranslations::t('personal_information') }}</h2>
                <div class="coverage-vip10-grid coverage-vip10-grid-2">
                    <div>
                        <label class="coverage-vip10-label">{{ \App\Support\CoverageFormTranslations::t('first_name') }}</label>
                        <input type="text" name="first_name" required value="{{ old('first_name', $primary?->first_name) }}" class="{{ $inputClass }}">
                    </div>
                    <div>
                        <label class="coverage-vip10-label">{{ \App\Support\CoverageFormTranslations::t('last_name') }}</label>
                        <input type="text" name="last_name" required value="{{ old('last_name', $primary?->last_name) }}" class="{{ $inputClass }}">
                    </div>
                    <div>
                        <label class="coverage-vip10-label">{{ \App\Support\CoverageFormTranslations::t('date_of_birth') }}</label>
                        <x-hero-date-input
                            name="date_of_birth"
                            :value="old('date_of_birth', optional($primary?->date_of_birth)?->format('Y-m-d'))"
                            maxDate="{{ now()->format('Y-m-d') }}"
                            required
                            :class="$inputClass"
                        />
                    </div>
                    <div>
                        <label class="coverage-vip10-label">{{ \App\Support\CoverageFormTranslations::t('local_address') }}</label>
                        <input type="text" name="street" required value="{{ old('street', $primary?->street) }}" class="{{ $inputClass }}">
                    </div>
                    <div>
                        <label class="coverage-vip10-label">{{ \App\Support\CoverageFormTranslations::t('city') }}</label>
                        <input type="text" name="city" required value="{{ old('city', $primary?->city) }}" class="{{ $inputClass }}">
                    </div>
                    <div>
                        <label class="coverage-vip10-label">{{ \App\Support\CoverageFormTranslations::t('zip_code') }}</label>
                        <input type="text" name="zip_code" required value="{{ old('zip_code', $primary?->zip_code) }}" class="{{ $inputClass }}">
                    </div>
                    <div>
                        <label class="coverage-vip10-label">{{ \App\Support\CoverageFormTranslations::t('phone') }}</label>
                        <input type="text" name="phone" required value="{{ old('phone', $primary?->phone) }}" class="{{ $inputClass }}">
                    </div>
                    <div>
                        <label class="coverage-vip10-label">{{ \App\Support\CoverageFormTranslations::t('email') }}</label>
                        <input type="email" name="email" required value="{{ old('email', $primary?->email) }}" class="{{ $inputClass }}">
                    </div>
                </div>
            </section>

            <section class="coverage-vip10-section">
                <h2 class="coverage-vip10-section-title">{{ \App\Support\CoverageFormTranslations::t('mailing_address') }}</h2>
                <div class="coverage-vip10-grid coverage-vip10-grid-1">
                    <div>
                        <label class="coverage-vip10-label">{{ \App\Support\CoverageFormTranslations::t('street') }}</label>
                        <input type="text" name="mailing_street" required value="{{ old('mailing_street', $profile?->mailing_street) }}" class="{{ $inputClass }}">
                    </div>
                    <div class="coverage-vip10-grid coverage-vip10-grid-2">
                        <div>
                            <label class="coverage-vip10-label">{{ \App\Support\CoverageFormTranslations::t('city') }}</label>
                            <input type="text" name="mailing_city" required value="{{ old('mailing_city', $profile?->mailing_city) }}" class="{{ $inputClass }}">
                        </div>
                        <div>
                            <label class="coverage-vip10-label">{{ \App\Support\CoverageFormTranslations::t('state') }}</label>
                            <input type="text" name="mailing_state" required value="{{ old('mailing_state', $profile?->mailing_state) }}" class="{{ $inputClass }}">
                        </div>
                    </div>
                    <div class="coverage-vip10-grid coverage-vip10-grid-2">
                        <div>
                            <label class="coverage-vip10-label">{{ \App\Support\CoverageFormTranslations::t('zip_code') }}</label>
                            <input type="text" name="mailing_zip_code" required value="{{ old('mailing_zip_code', $profile?->mailing_zip_code) }}" class="{{ $inputClass }}">
                        </div>
                        <div>
                            <label class="coverage-vip10-label">{{ \App\Support\CoverageFormTranslations::t('country') }}</label>
                            <input type="text" name="mailing_country" required value="{{ old('mailing_country', $profile?->mailing_country) }}" class="{{ $inputClass }}">
                        </div>
                    </div>
                </div>
            </section>

            <section class="coverage-vip10-section">
                <h2 class="coverage-vip10-section-title">{{ \App\Support\CoverageFormTranslations::t('emergency_contact') }}</h2>
                <div class="coverage-vip10-grid coverage-vip10-grid-3">
                    <div>
                        <label class="coverage-vip10-label">{{ \App\Support\CoverageFormTranslations::t('first_name') }}</label>
                        <input type="text" name="emergency_contact_first_name" required value="{{ old('emergency_contact_first_name', $profile?->emergency_contact_first_name) }}" class="{{ $inputClass }}">
                    </div>
                    <div>
                        <label class="coverage-vip10-label">{{ \App\Support\CoverageFormTranslations::t('last_name') }}</label>
                        <input type="text" name="emergency_contact_last_name" required value="{{ old('emergency_contact_last_name', $profile?->emergency_contact_last_name) }}" class="{{ $inputClass }}">
                    </div>
                    <div>
                        <label class="coverage-vip10-label">{{ \App\Support\CoverageFormTranslations::t('phone') }}</label>
                        <input type="text" name="emergency_contact_phone" required value="{{ old('emergency_contact_phone', $profile?->emergency_contact_phone) }}" class="{{ $inputClass }}">
                    </div>
                </div>
            </section>

            <section class="coverage-vip10-section">
                <h2 class="coverage-vip10-section-title">{{ \App\Support\CoverageFormTranslations::t('trips') }}</h2>
                <div class="coverage-vip10-trip-table">
                    <div class="coverage-vip10-trip-head">
                        <span>{{ \App\Support\CoverageFormTranslations::t('trip_from') }}</span>
                        <span>{{ \App\Support\CoverageFormTranslations::t('trip_price') }}</span>
                        <span>{{ \App\Support\CoverageFormTranslations::t('trip_date') }}</span>
                        <span></span>
                    </div>
                    <template x-for="(row, index) in trips" :key="index">
                        <div class="coverage-vip10-trip-row">
                            <input type="text" class="{{ $inputClass }}" x-model="row.from" :name="`trips[${index}][from]`" required placeholder="{{ \App\Support\CoverageFormTranslations::t('trip_from') }}">
                            <input type="text" class="{{ $inputClass }}" x-model="row.price" :name="`trips[${index}][price]`" placeholder="{{ \App\Support\CoverageFormTranslations::t('trip_price') }}">
                            <input type="text" class="{{ $inputClass }} hero-date-input" x-model="row.date" :name="`trips[${index}][date]`" required autocomplete="off" placeholder="{{ \App\Support\CoverageFormTranslations::t('trip_date') }}">
                            <button type="button" class="coverage-vip10-remove" x-show="trips.length > 1" @click="removeTrip(index)">{{ \App\Support\CoverageFormTranslations::t('remove') }}</button>
                        </div>
                    </template>
                </div>
                <button type="button" class="coverage-vip10-add" @click="addTrip()">{{ \App\Support\CoverageFormTranslations::t('add_more') }}</button>
                <div class="coverage-vip10-total">
                    <label class="coverage-vip10-label">{{ \App\Support\CoverageFormTranslations::t('trip_total') }}</label>
                    <input type="text" name="trip_total" value="{{ old('trip_total', $profile?->trip_details['total'] ?? '') }}" class="{{ $inputClass }}">
                </div>
            </section>

            <section class="coverage-vip10-section">
                <h2 class="coverage-vip10-section-title">{{ \App\Support\CoverageFormTranslations::t('passport_section') }}</h2>
                <p class="coverage-vip10-notice">{{ \App\Support\CoverageFormTranslations::t('passport_name_notice') }}</p>
                <div class="coverage-vip10-grid coverage-vip10-grid-2">
                    <div>
                        <label class="coverage-vip10-label">{{ \App\Support\CoverageFormTranslations::t('country_of_citizenship') }}</label>
                        <input type="text" name="nationality" required value="{{ old('nationality', $primary?->nationality) }}" class="{{ $inputClass }}">
                    </div>
                    <div>
                        <label class="coverage-vip10-label">{{ \App\Support\CoverageFormTranslations::t('passport_issued_by') }}</label>
                        <input type="text" name="passport_issued_by" required value="{{ old('passport_issued_by', $profile?->passport_issued_by) }}" class="{{ $inputClass }}">
                    </div>
                    <div class="coverage-vip10-grid-span-2">
                        <label class="coverage-vip10-label">{{ \App\Support\CoverageFormTranslations::t('id_number') }}</label>
                        <input type="text" name="id_number" required value="{{ old('id_number', $primary?->id_number) }}" class="{{ $inputClass }}">
                    </div>
                </div>
            </section>

            <section class="coverage-vip10-section">
                <h2 class="coverage-vip10-section-title">{{ \App\Support\CoverageFormTranslations::t('medical_section') }}</h2>
                <div class="coverage-vip10-grid coverage-vip10-grid-1">
                    <div>
                        <label class="coverage-vip10-label">{{ \App\Support\CoverageFormTranslations::t('allergies') }}</label>
                        <input type="text" name="allergies" value="{{ old('allergies', $profile?->allergies) }}" class="{{ $inputClass }}" placeholder="{{ \App\Support\CoverageFormTranslations::t('allergies_placeholder') }}">
                    </div>
                    <div>
                        <label class="coverage-vip10-label">{{ \App\Support\CoverageFormTranslations::t('chronic_conditions') }}</label>
                        <textarea name="chronic_conditions" rows="3" class="{{ $inputClass }} coverage-vip10-textarea">{{ old('chronic_conditions', $profile?->chronic_conditions) }}</textarea>
                    </div>
                    <div>
                        <p class="coverage-vip10-checklist-title">{{ \App\Support\CoverageFormTranslations::t('medical_checklist') }}</p>
                        <div class="coverage-vip10-checklist">
                            @foreach ($medicalConditionFlags as $flag => $labels)
                                <label class="coverage-vip10-check">
                                    <input type="checkbox" name="medical_condition_flags[]" value="{{ $flag }}" @checked(in_array($flag, $selectedFlags, true))>
                                    <span>{{ $labels['en'] }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div>
                        <label class="coverage-vip10-label">{{ \App\Support\CoverageFormTranslations::t('other_medical') }}</label>
                        <textarea name="other_medical_info" rows="3" class="{{ $inputClass }} coverage-vip10-textarea">{{ old('other_medical_info', $profile?->other_medical_info) }}</textarea>
                    </div>
                </div>
            </section>

            <section class="coverage-vip10-section">
                <h2 class="coverage-vip10-section-title">{{ \App\Support\CoverageFormTranslations::t('travel_preferences_section') }}</h2>
                <div class="coverage-vip10-checklist">
                    @foreach ($travelPreferences as $key => $labels)
                        <label class="coverage-vip10-check">
                            <input type="checkbox" name="travel_preferences[]" value="{{ $key }}" @checked(in_array($key, $selectedPreferences, true))>
                            <span>{{ $labels['en'] }}</span>
                        </label>
                    @endforeach
                </div>
            </section>

            <section class="coverage-vip10-section">
                <h2 class="coverage-vip10-section-title">{{ \App\Support\CoverageFormTranslations::t('terms_section') }}</h2>
                <div class="coverage-vip10-terms-box">
                    @include('customer.membership.partials.coverage-terms-content')
                </div>
                <label class="coverage-vip10-check coverage-vip10-check-block">
                    <input type="checkbox" name="terms_accepted" value="1" required @checked(old('terms_accepted') || $profile?->terms_accepted_at)>
                    <span>{{ \App\Support\CoverageFormTranslations::t('terms_accept') }}</span>
                </label>
            </section>

            <section class="coverage-vip10-section">
                <h2 class="coverage-vip10-section-title">{{ \App\Support\CoverageFormTranslations::t('signature_section') }}</h2>
                <div class="coverage-vip10-grid coverage-vip10-grid-2">
                    <div>
                        <label class="coverage-vip10-label">{{ \App\Support\CoverageFormTranslations::t('applicant_signature') }}</label>
                        <input type="text" name="applicant_signature" required value="{{ old('applicant_signature', $profile?->applicant_signature) }}" class="{{ $inputClass }}">
                    </div>
                    <div>
                        <label class="coverage-vip10-label">{{ \App\Support\CoverageFormTranslations::t('signature_date') }}</label>
                        <x-hero-date-input
                            name="signature_date"
                            :value="old('signature_date', optional($profile?->signature_date)?->format('Y-m-d'))"
                            maxDate="{{ now()->format('Y-m-d') }}"
                            required
                            :class="$inputClass"
                        />
                    </div>
                </div>
            </section>

            <div class="coverage-vip10-submit-wrap">
                <button type="submit" class="coverage-vip10-submit">
                    {{ \App\Support\CoverageFormTranslations::t('continue') }}
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('vip10DayCoverageForm', (initialRows) => ({
            trips: initialRows,
            addTrip() {
                this.trips.push({ from: '', price: '', date: '' });
                this.$nextTick(() => window.initHeroDatePickers?.(this.$root));
            },
            removeTrip(index) {
                if (this.trips.length > 1) {
                    this.trips.splice(index, 1);
                }
            },
        }));
    });
</script>
@endpush
