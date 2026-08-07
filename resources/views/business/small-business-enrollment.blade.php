@php
    $inputClass = 'smb-enrollment-input';
    $rowDefaults = ['first_name' => '', 'last_name' => '', 'plan_id' => '', 'date_of_birth' => ''];

    $workplaceRows = old('workplace_enrollments');
    $workplaceRows = is_array($workplaceRows) && $workplaceRows !== []
        ? $workplaceRows
        : ($profile?->workplace_enrollments ?? [$rowDefaults]);

    $managerRows = old('manager_enrollments');
    $managerRows = is_array($managerRows) && $managerRows !== []
        ? $managerRows
        : ($profile?->manager_enrollments ?? [$rowDefaults]);
@endphp

<x-portal-layout>
    <div class="smb-enrollment-page">
        @if (session('status'))
            <div class="smb-enrollment-alert smb-enrollment-alert-success">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="smb-enrollment-alert smb-enrollment-alert-error">
                <ul class="list-disc space-y-1 pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="smb-enrollment-card">
            <header class="smb-enrollment-header">
                <div class="smb-enrollment-logo" aria-hidden="true">
                    <i class="fa-solid fa-heart-pulse"></i>
                </div>
                <h1 class="smb-enrollment-title">{{ $formTitle }}</h1>
            </header>

            <form
                method="POST"
                action="{{ route('business.small-business.enrollment.update') }}"
                class="smb-enrollment-form"
                x-data="smallBusinessEnrollmentForm(@js($workplaceRows), @js($managerRows))"
            >
                @csrf
                @method('PUT')

                <section class="smb-enrollment-section">
                    <label class="smb-enrollment-label">{{ \App\Support\SmallBusinessFormTranslations::en('business_name') }} *</label>
                    <div class="smb-enrollment-input-wrap">
                        <i class="fa-solid fa-building smb-enrollment-input-icon" aria-hidden="true"></i>
                        <input type="text" name="business_name" required value="{{ old('business_name', $profile?->business_name ?: $company->name) }}" class="{{ $inputClass }}">
                    </div>
                </section>

                <section class="smb-enrollment-section">
                    <div class="smb-enrollment-grid smb-enrollment-grid-2">
                        <div>
                            <label class="smb-enrollment-label">{{ \App\Support\SmallBusinessFormTranslations::en('first_name') }}</label>
                            <div class="smb-enrollment-input-wrap">
                                <i class="fa-solid fa-user smb-enrollment-input-icon" aria-hidden="true"></i>
                                <input type="text" name="contact_first_name" required value="{{ old('contact_first_name', $profile?->contact_first_name) }}" class="{{ $inputClass }}">
                            </div>
                        </div>
                        <div>
                            <label class="smb-enrollment-label">{{ \App\Support\SmallBusinessFormTranslations::en('last_name') }}</label>
                            <input type="text" name="contact_last_name" required value="{{ old('contact_last_name', $profile?->contact_last_name) }}" class="{{ $inputClass }}">
                        </div>
                        <div>
                            <label class="smb-enrollment-label">{{ \App\Support\SmallBusinessFormTranslations::en('position') }}</label>
                            <div class="smb-enrollment-input-wrap">
                                <i class="fa-solid fa-id-badge smb-enrollment-input-icon" aria-hidden="true"></i>
                                <input type="text" name="contact_position" value="{{ old('contact_position', $profile?->contact_position) }}" class="{{ $inputClass }}">
                            </div>
                        </div>
                        <div>
                            <label class="smb-enrollment-label">{{ \App\Support\SmallBusinessFormTranslations::en('phone') }}</label>
                            <div class="smb-enrollment-input-wrap">
                                <i class="fa-solid fa-mobile-screen smb-enrollment-input-icon" aria-hidden="true"></i>
                                <input type="text" name="contact_phone" required value="{{ old('contact_phone', $profile?->contact_phone) }}" class="{{ $inputClass }}">
                            </div>
                        </div>
                    </div>
                </section>

                @include('business.partials.small-business-enrollment-tier', [
                    'tier' => 'workplace',
                    'titleKey' => 'onsite_workplace_coverage',
                    'plans' => $plansByTier->get('workplace', collect()),
                    'inputClass' => $inputClass,
                ])

                @include('business.partials.small-business-enrollment-tier', [
                    'tier' => 'manager',
                    'titleKey' => 'manager_plan',
                    'plans' => $plansByTier->get('manager', collect()),
                    'inputClass' => $inputClass,
                ])

                <section class="smb-enrollment-section">
                    <h2 class="smb-enrollment-section-title">{{ \App\Support\SmallBusinessFormTranslations::en('terms_section') }}</h2>
                    <div class="smb-enrollment-terms-box">
                        @include('business.partials.small-business-enrollment-terms')
                    </div>
                    <p class="smb-enrollment-note">{{ \App\Support\SmallBusinessFormTranslations::en('small_business_pricing_note') }}</p>
                    <label class="smb-enrollment-check">
                        <input type="checkbox" name="terms_accepted" value="1" required @checked(old('terms_accepted') || $profile?->terms_accepted_at)>
                        <span>{{ \App\Support\SmallBusinessFormTranslations::en('terms_accept') }}</span>
                    </label>
                </section>

                <div class="smb-enrollment-submit-wrap">
                    <button type="submit" class="smb-enrollment-submit">
                        {{ \App\Support\SmallBusinessFormTranslations::en('submit') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-portal-layout>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('smallBusinessEnrollmentForm', (workplace, manager) => ({
            workplace,
            manager,
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
