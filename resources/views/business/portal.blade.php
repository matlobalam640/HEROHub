<x-portal-layout>
    <div class="w-full max-w-none space-y-6">
        <div>
            <div class="text-sm font-medium text-hero-primary">Business / Corporate</div>
            <h1 class="font-display mt-1 text-2xl font-semibold tracking-tight text-slate-900">Company portal</h1>
            <p class="mt-1 text-sm text-slate-600">Manage employees, coverage, and billing for your organization.</p>
        </div>

        @if (session('status'))
            <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                {{ session('status') }}
            </div>
        @endif

        @if($errors->has('company'))
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                {{ $errors->first('company') }}
            </div>
        @endif

        @if($companies->isEmpty())
            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-6 text-slate-900">
                <p class="font-semibold">No company linked to your account</p>
                <p class="mt-1 text-sm text-slate-700">Ask a HERO administrator to create a company and set you as the HR owner (<code class="text-xs">owner_user_id</code>).</p>
            </div>
        @else
            <div class="flex flex-wrap items-end justify-between gap-4 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm">
                <form method="POST" action="{{ route('business.company.switch') }}" class="flex flex-wrap items-end gap-3">
                    @csrf
                    <div>
                        <label for="company_id" class="text-xs font-medium text-slate-600">Organization</label>
                        <select id="company_id" name="company_id" class="mt-1 min-w-[16rem] rounded-xl border-slate-300 text-sm shadow-sm focus:border-hero-primary focus:ring-hero-primary" onchange="this.form.submit()">
                            @foreach($companies as $c)
                                <option value="{{ $c->id }}" @selected($company && $company->id === $c->id)>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="rounded-xl bg-hero-primary px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-hero-primary-hover">Switch</button>
                </form>
            </div>

            @if($company)
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm">
                        <div class="text-xs font-medium uppercase tracking-wide text-slate-500">Active employees</div>
                        <div class="mt-1 text-2xl font-semibold text-slate-900">{{ $stats['active'] }}</div>
                        <div class="mt-2 text-xs text-slate-500">Used for billing</div>
                    </div>
                    <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm">
                        <div class="text-xs font-medium uppercase tracking-wide text-slate-500">Inactive / paused</div>
                        <div class="mt-1 text-2xl font-semibold text-slate-900">{{ $stats['inactive'] }}</div>
                    </div>
                    <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm">
                        <div class="text-xs font-medium uppercase tracking-wide text-slate-500">Other statuses</div>
                        <div class="mt-1 text-2xl font-semibold text-slate-900">{{ $stats['other'] }}</div>
                    </div>
                    <div class="rounded-2xl border border-slate-200/90 bg-hero-primary-soft p-5 shadow-sm ring-1 ring-slate-200/60">
                        <div class="text-xs font-medium uppercase tracking-wide text-hero-primary">Est. monthly total</div>
                        <div class="mt-1 text-2xl font-semibold text-hero-primary">${{ number_format((float) $company->billing_cached_monthly_total, 2) }}</div>
                        <div class="mt-2 text-xs text-slate-600">{{ $company->billing_cached_active_employees }} active × seat rate (see billing)</div>
                    </div>
                </div>

                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('business.employees.index') }}" class="inline-flex items-center gap-2 rounded-xl bg-hero-primary px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-hero-primary-hover">
                        <i class="fa-solid fa-users" aria-hidden="true"></i>
                        Employees &amp; coverage
                    </a>
                    <a href="{{ route('business.billing.edit') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm hover:border-hero-primary hover:text-hero-primary">
                        <i class="fa-solid fa-file-invoice-dollar" aria-hidden="true"></i>
                        Company billing
                    </a>
                </div>
            @endif
        @endif
    </div>
</x-portal-layout>
