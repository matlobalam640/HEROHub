<x-portal-layout>
    <div class="space-y-6">
        <div class="hero-dispatch-hero">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <div class="hero-dispatch-hero__eyebrow">Dispatch · Business enroll</div>
                    <h1 class="hero-dispatch-hero__title">Enroll business (B2B)</h1>
                    <p class="hero-dispatch-hero__lead">Capture company and HR contact details. We send a portal invite so HR can choose a Small Business or Corporate plan and pay with USA Payments.</p>
                </div>
                <div class="hidden shrink-0 sm:block" aria-hidden="true">
                    <span class="hero-dispatch-hero__icon">
                        <i class="fa-solid fa-building" aria-hidden="true"></i>
                    </span>
                </div>
            </div>
        </div>

        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
                {{ session('status') }}
            </div>
        @endif

        @if ($result)
            <div class="hero-portal-panel overflow-hidden">
                <div class="hero-panel-header border-b border-slate-100 px-6 py-4">
                    <div class="text-sm font-semibold text-slate-900">Enrollment started</div>
                </div>
                <div class="space-y-2 p-6 text-sm text-slate-700">
                    <p><strong>{{ $result['company_name'] }}</strong></p>
                    <p>HR: {{ $result['hr_name'] }} — {{ $result['hr_email'] }}</p>
                    <p>Status: <span class="font-semibold text-amber-700">Pending plan &amp; payment</span></p>
                    <p class="text-xs text-slate-500">{{ $result['owner_created'] ? 'Portal invite email sent with password setup link.' : 'Existing user linked — sign-in link emailed.' }}</p>
                    <a href="{{ route('admin.companies.index') }}" class="inline-flex text-sm font-semibold text-hero-primary hover:underline">Open companies list →</a>
                </div>
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-900">
                <ul class="list-disc space-y-1 pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.business-enrollment.store') }}" class="space-y-6">
            @csrf

            <div class="hero-portal-panel overflow-hidden">
                <div class="hero-panel-header border-b border-slate-100 px-6 py-4">
                    <div class="text-sm font-semibold text-slate-900">Company</div>
                </div>
                <div class="grid grid-cols-1 gap-4 p-6 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="company_name" class="block text-sm font-medium text-slate-700">Company name</label>
                        <input id="company_name" name="company_name" value="{{ old('company_name') }}" required
                               class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-hero-primary focus:ring-hero-primary">
                    </div>
                    <div>
                        <label for="billing_email" class="block text-sm font-medium text-slate-700">Billing email (optional)</label>
                        <input id="billing_email" name="billing_email" type="email" value="{{ old('billing_email') }}"
                               class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-hero-primary focus:ring-hero-primary">
                        <p class="mt-1 text-xs text-slate-500">Defaults to the HR email when left blank.</p>
                    </div>
                    <div>
                        <label for="company_phone" class="block text-sm font-medium text-slate-700">Company phone</label>
                        <input id="company_phone" name="company_phone" value="{{ old('company_phone') }}"
                               class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-hero-primary focus:ring-hero-primary">
                    </div>
                    <div>
                        <label for="company_city" class="block text-sm font-medium text-slate-700">City</label>
                        <input id="company_city" name="company_city" value="{{ old('company_city') }}"
                               class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-hero-primary focus:ring-hero-primary">
                    </div>
                    <div>
                        <label for="company_country" class="block text-sm font-medium text-slate-700">Country</label>
                        <input id="company_country" name="company_country" value="{{ old('company_country', 'USA') }}"
                               class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-hero-primary focus:ring-hero-primary">
                    </div>
                    <div class="sm:col-span-2">
                        <label for="address_line1" class="block text-sm font-medium text-slate-700">Address (optional)</label>
                        <input id="address_line1" name="address_line1" value="{{ old('address_line1') }}"
                               class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-hero-primary focus:ring-hero-primary">
                    </div>
                    <div>
                        <label for="postal_code" class="block text-sm font-medium text-slate-700">Postal code</label>
                        <input id="postal_code" name="postal_code" value="{{ old('postal_code') }}"
                               class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-hero-primary focus:ring-hero-primary">
                    </div>
                </div>
            </div>

            <div class="hero-portal-panel overflow-hidden">
                <div class="hero-panel-header border-b border-slate-100 px-6 py-4">
                    <div class="text-sm font-semibold text-slate-900">HR / portal contact</div>
                    <p class="mt-1 text-xs text-slate-500">This person receives the invite and manages the company portal.</p>
                </div>
                <div class="grid grid-cols-1 gap-4 p-6 sm:grid-cols-2">
                    <div>
                        <label for="hr_first_name" class="block text-sm font-medium text-slate-700">First name</label>
                        <input id="hr_first_name" name="hr_first_name" value="{{ old('hr_first_name') }}" required
                               class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-hero-primary focus:ring-hero-primary">
                    </div>
                    <div>
                        <label for="hr_last_name" class="block text-sm font-medium text-slate-700">Last name</label>
                        <input id="hr_last_name" name="hr_last_name" value="{{ old('hr_last_name') }}" required
                               class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-hero-primary focus:ring-hero-primary">
                    </div>
                    <div class="sm:col-span-2">
                        <label for="hr_email" class="block text-sm font-medium text-slate-700">Email (portal login)</label>
                        <input id="hr_email" name="hr_email" type="email" value="{{ old('hr_email') }}" required
                               class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-hero-primary focus:ring-hero-primary">
                    </div>
                    <div class="sm:col-span-2">
                        <label for="hr_phone" class="block text-sm font-medium text-slate-700">Phone (optional)</label>
                        <input id="hr_phone" name="hr_phone" value="{{ old('hr_phone') }}"
                               class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-hero-primary focus:ring-hero-primary">
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap gap-3">
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-hero-primary px-5 py-2.5 text-sm font-semibold text-white shadow-hero-cta hover:bg-hero-primary-hover">
                    Create company &amp; send invite
                </button>
            </div>
        </form>
    </div>
</x-portal-layout>
