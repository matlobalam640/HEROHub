<section class="coverage-vip-section">
    <h2 class="coverage-vip-section-title">{{ \App\Support\CoverageFormTranslations::en('health_questionnaire_section') }}</h2>
    <div class="coverage-vip-questionnaire">
        @foreach ($individualHealthQuestionnaire as $key => $labels)
            <div>
                <p class="coverage-vip-question-text">{{ $labels['en'] }}</p>
                <textarea
                    name="health_questionnaire[{{ $key }}]"
                    rows="2"
                    required
                    class="{{ $inputClass }}"
                >{{ old('health_questionnaire.'.$key, $savedQuestionnaire[$key] ?? '') }}</textarea>
            </div>
        @endforeach
    </div>
</section>
