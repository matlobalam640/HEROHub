<x-portal-layout>
    <div class="w-full max-w-none space-y-6">
        <div>
            @include('customer.membership.partials.portal-eyebrow')
            <h1 class="font-display mt-1 text-2xl font-semibold tracking-tight text-slate-900">Family members</h1>
            <p class="mt-1 text-sm text-slate-600">
                Manage household members on your {{ $membership->plan?->name ?? 'family' }} plan.
            </p>
        </div>

        @if (session('status'))
            <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2 space-y-6">
                <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-md shadow-slate-200/50 ring-1 ring-slate-100">
                    <div class="hero-panel-header flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 bg-gradient-to-r from-slate-50 to-[color:var(--dashboard-gold-soft)] px-6 py-4">
                        <div>
                            <div class="text-sm font-semibold text-slate-900">Current family members</div>
                            <div class="text-xs text-slate-500">Membership {{ $membership->membership_number }}</div>
                        </div>
                        <div class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-slate-700 ring-1 ring-slate-200">
                            {{ $dependentCount }} of {{ $includedDependentLimit }} included
                            <span class="font-normal text-slate-500">({{ $includedPlanCapacity }} on plan)</span>
                            @if ($planCapacity > $includedPlanCapacity)
                                <span class="font-normal text-slate-500">· max {{ $dependentLimit }} ({{ $planCapacity }} total)</span>
                            @endif
                        </div>
                    </div>
                    <div class="p-6">
                        @if ($familyDependents->isNotEmpty())
                            <ul class="divide-y divide-slate-100 rounded-xl border border-slate-200">
                                @foreach ($familyDependents as $dep)
                                    <li class="flex items-start justify-between gap-4 px-4 py-3">
                                        <div>
                                            <div class="font-medium text-slate-900">{{ $dep->first_name }} {{ $dep->last_name }}</div>
                                            <div class="mt-0.5 text-xs text-slate-600">
                                                {{ \App\Support\HouseholdDependentFormOptions::relationshipLabel($dep->relationship) }}
                                                @if ($dep->date_of_birth)
                                                    · {{ $dep->date_of_birth->format('M j, Y') }}
                                                @endif
                                                @if ($dep->gender)
                                                    · {{ \App\Support\HouseholdDependentFormOptions::genderLabel($dep->gender) }}
                                                @endif
                                            </div>
                                        </div>
                                        <form method="POST" action="{{ route('customer.membership.dependents.destroy', ['dependentId' => $dep->id]) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-md border border-red-200 px-2 py-1 text-xs font-semibold text-red-700 hover:bg-red-50">Remove</button>
                                        </form>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="rounded-xl border border-dashed border-slate-200 bg-slate-50 px-4 py-6 text-center text-sm text-slate-600">
                                No family members added yet. Use the form below to add dependents covered under your plan.
                            </p>
                        @endif
                    </div>
                </div>

                <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-md shadow-slate-200/50 ring-1 ring-slate-100">
                    <div class="hero-panel-header border-b border-slate-100 bg-gradient-to-r from-slate-50 to-[color:var(--dashboard-gold-soft)] px-6 py-4">
                        <div class="text-sm font-semibold text-slate-900">Add family member</div>
                        @if (! $canAddMoreDependents)
                            <p class="mt-1 text-xs text-amber-800">You have reached the maximum for your plan.</p>
                        @endif
                    </div>
                    <div class="p-6">
                        @if ($canAddMoreDependents)
                            <form method="POST" action="{{ route('customer.membership.dependents.store') }}" class="space-y-4">
                                @csrf
                                @include('customer.membership.partials.household-dependent-fields', [
                                    'inputClass' => 'w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:border-hero-primary focus:outline-none',
                                    'values' => old(),
                                ])
                                <div>
                                    <button type="submit" class="rounded-lg bg-hero-primary px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-hero-primary-hover">
                                        Add family member
                                    </button>
                                </div>
                            </form>
                        @else
                            <p class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-950">
                                Your plan includes up to <strong>{{ $planCapacity }}</strong> people total (primary member plus {{ $dependentLimit }} dependents). Remove a member to add another, or upgrade your plan for additional capacity.
                            </p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-md shadow-slate-200/50 ring-1 ring-slate-100">
                <div class="hero-panel-header border-b border-slate-100 bg-gradient-to-r from-slate-50 to-[color:var(--dashboard-gold-soft)] px-6 py-4">
                    <div class="text-sm font-semibold text-slate-900">Plan</div>
                    <div class="text-xs text-slate-500">Coverage type</div>
                </div>
                <div class="p-6 text-sm text-slate-700">
                    <p class="font-medium text-slate-900">{{ $membership->plan?->name ?? '—' }}</p>
                    <dl class="mt-4 space-y-2 text-xs">
                        <div class="flex justify-between gap-3">
                            <dt class="text-slate-500">Included on plan</dt>
                            <dd class="font-medium text-slate-900">{{ $includedPlanCapacity }} people ({{ $includedDependentLimit }} dependents)</dd>
                        </div>
                        @if ($planCapacity > $includedPlanCapacity)
                            <div class="flex justify-between gap-3">
                                <dt class="text-slate-500">Maximum with add-ons</dt>
                                <dd class="font-medium text-slate-900">{{ $planCapacity }} people ({{ $dependentLimit }} dependents)</dd>
                            </div>
                        @endif
                        <div class="flex justify-between gap-3">
                            <dt class="text-slate-500">Dependents added</dt>
                            <dd class="font-medium text-slate-900">{{ $dependentCount }}</dd>
                        </div>
                    </dl>
                    <div class="mt-5">
                        <a href="{{ route('customer.membership') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-700 shadow-sm transition hover:border-hero-primary hover:text-hero-primary">
                            <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                            Back to membership
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-portal-layout>
