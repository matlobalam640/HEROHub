<x-portal-layout>
    <div class="w-full max-w-none space-y-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <div class="text-sm font-medium text-hero-primary">Business / Corporate</div>
                <h1 class="font-display mt-1 text-2xl font-semibold tracking-tight text-slate-900">Company billing</h1>
                <p class="mt-1 text-sm text-slate-600">{{ $company->name }}</p>
            </div>
            <a href="{{ route('business.portal') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 shadow-sm hover:border-hero-primary">← Portal</a>
        </div>

        @if (session('status'))
            <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('status') }}</div>
        @endif

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2 overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
                <h2 class="text-sm font-semibold text-slate-900">Billing settings</h2>
                <p class="mt-1 text-xs text-slate-500">Default plan is used for CSV imports when a row has no plan. Per-employee override (optional) replaces plan-based seat pricing in the estimate.</p>

                <form method="POST" action="{{ route('business.billing.update') }}" class="mt-6 space-y-4">
                    @csrf
                    @method('PATCH')

                    <div>
                        <label for="billing_email" class="text-xs font-medium text-slate-600">Billing email</label>
                        <input id="billing_email" name="billing_email" type="email" value="{{ old('billing_email', $company->billing_email) }}" class="mt-1 w-full max-w-md rounded-lg border-slate-300 text-sm focus:border-hero-primary focus:ring-hero-primary">
                    </div>

                    <div>
                        <label for="default_plan_id" class="text-xs font-medium text-slate-600">Default enrollment plan</label>
                        <select id="default_plan_id" name="default_plan_id" class="mt-1 w-full max-w-md rounded-lg border-slate-300 text-sm focus:border-hero-primary focus:ring-hero-primary">
                            <option value="">— None —</option>
                            @foreach($plans as $plan)
                                <option value="{{ $plan->id }}" @selected(old('default_plan_id', $company->default_plan_id) == $plan->id)>{{ $plan->name }} ({{ $plan->code }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="billing_per_employee_override" class="text-xs font-medium text-slate-600">Per active employee / month (override, USD)</label>
                        <input id="billing_per_employee_override" name="billing_per_employee_override" type="text" inputmode="decimal" placeholder="Leave empty to use plan pricing" value="{{ old('billing_per_employee_override', $company->billing_per_employee_override) }}" class="mt-1 w-full max-w-md rounded-lg border-slate-300 text-sm focus:border-hero-primary focus:ring-hero-primary">
                        <p class="mt-1 text-xs text-slate-500">When set, estimated monthly total = active employees × this amount.</p>
                    </div>

                    <button type="submit" class="rounded-lg bg-hero-primary px-4 py-2 text-sm font-semibold text-white">Save &amp; recalculate</button>
                </form>
            </div>

            <div class="overflow-hidden rounded-2xl border border-slate-200/90 bg-hero-primary-soft p-6 shadow-sm ring-1 ring-slate-200/60">
                <h2 class="text-sm font-semibold text-hero-primary">Auto-calculated billing</h2>
                <p class="mt-1 text-xs text-slate-600">Based on <strong>active</strong> employee memberships only. Updates when you add/remove employees or change status.</p>
                <dl class="mt-4 space-y-3 text-sm">
                    <div>
                        <dt class="text-xs font-medium text-slate-600">Active employees</dt>
                        <dd class="text-2xl font-semibold text-hero-primary">{{ $company->billing_cached_active_employees }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-slate-600">Est. monthly total (USD)</dt>
                        <dd class="text-2xl font-semibold text-hero-primary">${{ number_format((float) $company->billing_cached_monthly_total, 2) }}</dd>
                    </div>
                    @if($company->billing_calculated_at)
                        <div class="text-xs text-slate-500">Last calculated {{ $company->billing_calculated_at->diffForHumans() }}</div>
                    @endif
                </dl>
                <a href="{{ route('business.employees.index') }}" class="mt-4 inline-flex text-xs font-semibold text-hero-primary underline">Manage employees →</a>
            </div>
        </div>
    </div>
</x-portal-layout>
