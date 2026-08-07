<x-portal-layout>
    <div class="w-full max-w-none space-y-5">
        <div>
            @include('customer.membership.partials.portal-eyebrow')
            <h1 class="font-display mt-1 text-2xl font-semibold tracking-tight text-slate-900">{{ $formTitle }}</h1>
            @unless ($usesFamilyForm)
                <p class="mt-2 text-sm text-slate-600">{{ \App\Support\CoverageFormTranslations::en('individual_intro') }}</p>
            @endunless
        </div>

        @if (session('status'))
            <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                <ul class="list-disc space-y-1 pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($usesFamilyForm)
            @include('customer.membership.partials.coverage-information-family-form')
        @else
            @include('customer.membership.partials.coverage-information-individual-form')
        @endif
    </div>
</x-portal-layout>
