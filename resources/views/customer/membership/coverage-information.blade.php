<x-portal-layout>
    @php
        $extendedForm = $usesVip10DayForm || $usesVipIndividualForm || $usesIndividualPlanForm;
        $pageClass = match (true) {
            $usesVip10DayForm => 'coverage-vip10-page',
            $usesVipIndividualForm => 'coverage-vip-page',
            $usesIndividualPlanForm => 'coverage-individual-plan-page',
            default => 'space-y-5',
        };
    @endphp
    <div class="w-full max-w-none {{ $pageClass }}">
        @unless ($extendedForm)
            <div>
                @include('customer.membership.partials.portal-eyebrow')
                <h1 class="font-display mt-1 text-2xl font-semibold tracking-tight text-slate-900">{{ $formTitle }}</h1>
                @unless ($usesFamilyForm)
                    <p class="mt-2 text-sm text-slate-600">{{ \App\Support\CoverageFormTranslations::en('individual_intro') }}</p>
                @endunless
            </div>
        @endunless

        @if (session('status'))
            <div class="rounded-xl border px-4 py-3 text-sm {{ $usesVip10DayForm ? 'coverage-vip10-alert border-green-300/40 bg-green-900/30 text-green-100' : 'border-green-200 bg-green-50 text-green-800' }} {{ ($usesVipIndividualForm || $usesIndividualPlanForm) ? 'coverage-vip-alert' : '' }}">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-xl border px-4 py-3 text-sm {{ $usesVip10DayForm ? 'coverage-vip10-alert border-red-300/40 bg-red-900/30 text-red-100' : 'border-red-200 bg-red-50 text-red-800' }} {{ ($usesVipIndividualForm || $usesIndividualPlanForm) ? 'coverage-vip-alert' : '' }}">
                <ul class="list-disc space-y-1 pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($usesVip10DayForm)
            @include('customer.membership.partials.coverage-information-vip-10-day-form')
        @elseif ($usesVipIndividualForm)
            @include('customer.membership.partials.coverage-information-vip-individual-form')
        @elseif ($usesIndividualPlanForm)
            @include('customer.membership.partials.coverage-information-individual-plan-form')
        @elseif ($usesFamilyForm)
            @include('customer.membership.partials.coverage-information-family-form')
        @else
            @include('customer.membership.partials.coverage-information-individual-form')
        @endif
    </div>
</x-portal-layout>
