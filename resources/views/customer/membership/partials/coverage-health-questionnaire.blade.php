<section class="coverage-vip-section">
    <h2 class="coverage-vip-section-title">{{ \App\Support\CoverageFormTranslations::en('health_questionnaire_section') }}</h2>
    <div class="coverage-vip-questionnaire">
        @foreach ($healthQuestionnaire as $key => $labels)
            @php
                $selected = old('health_questionnaire.'.$key, $savedQuestionnaire[$key] ?? null);
            @endphp
            <div class="coverage-vip-question">
                <p class="coverage-vip-question-text">{{ $labels['en'] }}</p>
                <div class="coverage-vip-question-options">
                    <label class="coverage-vip-radio">
                        <input type="radio" name="health_questionnaire[{{ $key }}]" value="yes" @checked($selected === 'yes') required>
                        <span>{{ \App\Support\CoverageFormTranslations::en('answer_yes') }}</span>
                    </label>
                    <label class="coverage-vip-radio">
                        <input type="radio" name="health_questionnaire[{{ $key }}]" value="no" @checked($selected === 'no') required>
                        <span>{{ \App\Support\CoverageFormTranslations::en('answer_no') }}</span>
                    </label>
                </div>
            </div>
        @endforeach
    </div>
</section>
