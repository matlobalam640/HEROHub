<section class="corporate-enrollment-section">
    <div class="corporate-enrollment-section-head">
        <h2 class="corporate-enrollment-section-title">{{ \App\Support\CorporateFormTranslations::en($titleKey) }}</h2>
        <button type="button" class="corporate-enrollment-add" @click="addTier('{{ $tier }}')">
            <i class="fa-solid fa-circle-plus" aria-hidden="true"></i>
            {{ \App\Support\CorporateFormTranslations::en('add_more') }}
        </button>
    </div>
    <p class="corporate-enrollment-help">{{ \App\Support\CorporateFormTranslations::en('section_help') }}</p>

    <div class="corporate-enrollment-tier-table">
        <div class="corporate-enrollment-tier-head">
            <span>{{ \App\Support\CorporateFormTranslations::en('name') }}</span>
            <span>{{ \App\Support\CorporateFormTranslations::en('plans') }}</span>
            <span>{{ \App\Support\CorporateFormTranslations::en('date_of_birth') }}</span>
            <span></span>
        </div>

        <template x-for="(row, index) in {{ $tier }}" :key="'{{ $tier }}-' + index">
            <div class="corporate-enrollment-tier-row">
                <div class="corporate-enrollment-name-stack">
                    <input type="text" class="{{ $inputClass }}" x-model="row.first_name" :name="`{{ $tier }}_enrollments[${index}][first_name]`" placeholder="{{ \App\Support\CorporateFormTranslations::en('first_name') }}">
                    <input type="text" class="{{ $inputClass }}" x-model="row.last_name" :name="`{{ $tier }}_enrollments[${index}][last_name]`" placeholder="{{ \App\Support\CorporateFormTranslations::en('last_name') }}">
                </div>
                <select class="{{ $inputClass }} corporate-enrollment-select" x-model="row.plan_id" :name="`{{ $tier }}_enrollments[${index}][plan_id]`">
                    <option value="">{{ \App\Support\CorporateFormTranslations::en('select_plan') }}</option>
                    @foreach ($plans as $plan)
                        <option value="{{ $plan->id }}">{{ $plan->name }} ({{ $plan->code }})</option>
                    @endforeach
                </select>
                <input type="text" class="{{ $inputClass }} hero-date-input" x-model="row.date_of_birth" :name="`{{ $tier }}_enrollments[${index}][date_of_birth]`" data-max-date="{{ now()->format('Y-m-d') }}" autocomplete="off" placeholder="{{ \App\Support\CorporateFormTranslations::en('date_of_birth') }}">
                <button type="button" class="corporate-enrollment-remove" x-show="{{ $tier }}.length > 1" @click="removeTier('{{ $tier }}', index)">
                    {{ \App\Support\CorporateFormTranslations::en('remove') }}
                </button>
            </div>
        </template>
    </div>
</section>
