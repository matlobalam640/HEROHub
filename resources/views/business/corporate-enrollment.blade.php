@php
    $inputClass = 'corporate-enrollment-input';
    $rowDefaults = ['first_name' => '', 'last_name' => '', 'plan_id' => '', 'date_of_birth' => ''];

    $workplaceRows = old('workplace_enrollments');
    $workplaceRows = is_array($workplaceRows) && $workplaceRows !== []
        ? $workplaceRows
        : ($profile?->workplace_enrollments ?? [$rowDefaults]);

    $managerRows = old('manager_enrollments');
    $managerRows = is_array($managerRows) && $managerRows !== []
        ? $managerRows
        : ($profile?->manager_enrollments ?? [$rowDefaults]);

    $executiveRows = old('executive_enrollments');
    $executiveRows = is_array($executiveRows) && $executiveRows !== []
        ? $executiveRows
        : ($profile?->executive_enrollments ?? [$rowDefaults]);
@endphp

<x-portal-layout>
    <div class="corporate-enrollment-page">
        @if (session('status'))
            <div class="corporate-enrollment-alert corporate-enrollment-alert-success">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="corporate-enrollment-alert corporate-enrollment-alert-error">
                <ul class="list-disc space-y-1 pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="corporate-enrollment-shell">
            <aside class="corporate-enrollment-art" aria-hidden="true">
                <div class="corporate-enrollment-art-inner">
                    <div class="corporate-enrollment-art-icon">
                        <i class="fa-solid fa-user-doctor" aria-hidden="true"></i>
                    </div>
                    <p class="corporate-enrollment-art-caption">HERO emergency response for your workforce</p>
                </div>
            </aside>

            <div class="corporate-enrollment-form-column">
                <header class="corporate-enrollment-header">
                    <h1 class="corporate-enrollment-title">{{ $formTitle }}</h1>
                    <p class="corporate-enrollment-company">{{ $company->name }}</p>
                </header>

                <form
                    method="POST"
                    action="{{ route('business.enrollment.update') }}"
                    class="corporate-enrollment-form"
                    x-data="corporateEnrollmentForm(@js($workplaceRows), @js($managerRows), @js($executiveRows))"
                >
                    @csrf
                    @method('PUT')

                    <section class="corporate-enrollment-section">
                        <h2 class="corporate-enrollment-section-title">{{ \App\Support\CorporateFormTranslations::en('contact_information') }}</h2>
                        <div class="corporate-enrollment-grid corporate-enrollment-grid-2">
                            <div>
                                <label class="corporate-enrollment-label">{{ \App\Support\CorporateFormTranslations::en('first_name') }}</label>
                                <input type="text" name="contact_first_name" required value="{{ old('contact_first_name', $profile?->contact_first_name) }}" class="{{ $inputClass }}">
                            </div>
                            <div>
                                <label class="corporate-enrollment-label">{{ \App\Support\CorporateFormTranslations::en('last_name') }}</label>
                                <input type="text" name="contact_last_name" required value="{{ old('contact_last_name', $profile?->contact_last_name) }}" class="{{ $inputClass }}">
                            </div>
                            <div>
                                <label class="corporate-enrollment-label">{{ \App\Support\CorporateFormTranslations::en('position') }}</label>
                                <input type="text" name="contact_position" value="{{ old('contact_position', $profile?->contact_position) }}" class="{{ $inputClass }}">
                            </div>
                            <div>
                                <label class="corporate-enrollment-label">{{ \App\Support\CorporateFormTranslations::en('phone') }}</label>
                                <input type="text" name="contact_phone" required value="{{ old('contact_phone', $profile?->contact_phone) }}" class="{{ $inputClass }}">
                            </div>
                        </div>
                    </section>

                    @include('business.partials.corporate-enrollment-tier', [
                        'tier' => 'workplace',
                        'titleKey' => 'workplace_coverage',
                        'plans' => $plansByTier->get('workplace', collect()),
                        'inputClass' => $inputClass,
                    ])

                    @include('business.partials.corporate-enrollment-tier', [
                        'tier' => 'manager',
                        'titleKey' => 'manager_plans',
                        'plans' => $plansByTier->get('manager', collect()),
                        'inputClass' => $inputClass,
                    ])

                    @include('business.partials.corporate-enrollment-tier', [
                        'tier' => 'executive',
                        'titleKey' => 'executive_plans',
                        'plans' => $plansByTier->get('executive', collect()),
                        'inputClass' => $inputClass,
                    ])

                    <section class="corporate-enrollment-section">
                        <h2 class="corporate-enrollment-section-title">{{ \App\Support\CorporateFormTranslations::en('terms_section') }}</h2>
                        <div class="corporate-enrollment-terms-box">
                            @include('business.partials.corporate-enrollment-terms')
                        </div>
                        <p class="corporate-enrollment-note">{{ \App\Support\CorporateFormTranslations::en('corporate_pricing_note') }}</p>
                        <label class="corporate-enrollment-check">
                            <input type="checkbox" name="terms_accepted" value="1" required @checked(old('terms_accepted') || $profile?->terms_accepted_at)>
                            <span>{{ \App\Support\CorporateFormTranslations::en('terms_accept') }}</span>
                        </label>
                    </section>

                    <div class="corporate-enrollment-submit-wrap">
                        <button type="submit" class="corporate-enrollment-submit">
                            {{ \App\Support\CorporateFormTranslations::en('submit') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-portal-layout>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('corporateEnrollmentForm', (workplace, manager, executive) => ({
            workplace,
            manager,
            executive,
            addTier(tier) {
                this[tier].push({
                    first_name: '',
                    last_name: '',
                    plan_id: '',
                    date_of_birth: '',
                });
                this.$nextTick(() => window.initHeroDatePickers?.(this.$root));
            },
            removeTier(tier, index) {
                if (this[tier].length > 1) {
                    this[tier].splice(index, 1);
                }
            },
        }));
    });
</script>
@endpush
