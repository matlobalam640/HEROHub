<section class="smb-enrollment-section">
    <div class="smb-enrollment-section-head">
        <h2 class="smb-enrollment-section-title">{{ \App\Support\SmallBusinessFormTranslations::en($titleKey) }}</h2>
        <button type="button" class="smb-enrollment-add" @click="addTier('{{ $tier }}')">
            <i class="fa-solid fa-circle-plus" aria-hidden="true"></i>
            {{ \App\Support\SmallBusinessFormTranslations::en('add_more') }}
        </button>
    </div>

    <div class="smb-enrollment-tier-table">
        <div class="smb-enrollment-tier-head">
            <span>{{ \App\Support\SmallBusinessFormTranslations::en('name') }}</span>
            <span>{{ \App\Support\SmallBusinessFormTranslations::en('date_of_birth') }}</span>
            <span>{{ \App\Support\SmallBusinessFormTranslations::en('plans') }}</span>
            <span></span>
        </div>

        <template x-for="(row, index) in {{ $tier }}" :key="'{{ $tier }}-' + index">
            <div class="smb-enrollment-tier-row">
                <div class="smb-enrollment-name-stack">
                    <div class="smb-enrollment-input-wrap">
                        <i class="fa-solid fa-user smb-enrollment-input-icon" aria-hidden="true"></i>
                        <input type="text" class="{{ $inputClass }}" x-model="row.first_name" :name="`{{ $tier }}_enrollments[${index}][first_name]`" placeholder="{{ \App\Support\SmallBusinessFormTranslations::en('first_name') }}">
                    </div>
                    <input type="text" class="{{ $inputClass }}" x-model="row.last_name" :name="`{{ $tier }}_enrollments[${index}][last_name]`" placeholder="{{ \App\Support\SmallBusinessFormTranslations::en('last_name') }}">
                </div>
                <div class="smb-enrollment-input-wrap">
                    <i class="fa-regular fa-calendar smb-enrollment-input-icon" aria-hidden="true"></i>
                    <input type="text" class="{{ $inputClass }} hero-date-input" x-model="row.date_of_birth" :name="`{{ $tier }}_enrollments[${index}][date_of_birth]`" data-max-date="{{ now()->format('Y-m-d') }}" autocomplete="off" placeholder="dd-MMM-yyyy">
                </div>
                <select class="{{ $inputClass }} smb-enrollment-select" x-model="row.plan_id" :name="`{{ $tier }}_enrollments[${index}][plan_id]`">
                    <option value="">{{ \App\Support\SmallBusinessFormTranslations::en('select_plan') }}</option>
                    @foreach ($plans as $plan)
                        <option value="{{ $plan->id }}">{{ $plan->name }} ({{ $plan->code }})</option>
                    @endforeach
                </select>
                <button type="button" class="smb-enrollment-remove" x-show="{{ $tier }}.length > 1" @click="removeTier('{{ $tier }}', index)">
                    {{ \App\Support\SmallBusinessFormTranslations::en('remove') }}
                </button>
            </div>
        </template>
    </div>
</section>
