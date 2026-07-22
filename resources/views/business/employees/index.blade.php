<x-portal-layout>
    <div class="w-full max-w-none space-y-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <div class="text-sm font-medium text-hero-primary">Business / Corporate</div>
                <h1 class="font-display mt-1 text-2xl font-semibold tracking-tight text-slate-900">Employees &amp; coverage</h1>
                <p class="mt-1 text-sm text-slate-600">{{ $company->name }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('business.portal') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 shadow-sm hover:border-hero-primary">← Portal</a>
                <a href="{{ route('business.billing.edit') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 shadow-sm hover:border-hero-primary">Billing</a>
            </div>
        </div>

        @if (session('status'))
            <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('status') }}</div>
        @endif

        <style>
            .hero-coverage-filter a {
                display: inline-flex;
                align-items: center;
                border-radius: 9999px;
                padding: 0.25rem 0.75rem;
                font-size: 0.875rem;
                font-weight: 500;
                text-decoration: none;
                background-color: #f1f5f9;
                color: #334155;
                border: 1px solid rgb(226 232 240 / 0.85);
                transition: background-color 0.15s ease, color 0.15s ease, box-shadow 0.15s ease;
            }
            .hero-coverage-filter a:hover {
                background-color: #e2e8f0;
            }
            .hero-coverage-filter a[data-coverage-active] {
                background-color: #283b69;
                background-image: none;
                color: #fff !important;
                border-color: transparent;
                box-shadow: 0 6px 18px -8px rgba(40, 59, 105, 0.5);
            }
            .dark-theme .hero-coverage-filter a {
                background-color: #1e293b;
                color: #e2e8f0;
                border-color: #334155;
            }
            .dark-theme .hero-coverage-filter a:hover {
                background-color: #334155;
            }
            .dark-theme .hero-coverage-filter a[data-coverage-active] {
                background-color: #283b69;
                background-image: none;
                color: #fff !important;
                border-color: transparent;
            }
        </style>
        <nav class="hero-coverage-filter flex flex-wrap gap-2" aria-label="Coverage filter">
            <a href="{{ route('business.employees.index', ['coverage' => 'all']) }}" @if ($filter === 'all') data-coverage-active @endif>All</a>
            <a href="{{ route('business.employees.index', ['coverage' => 'active']) }}" @if ($filter === 'active') data-coverage-active @endif>Active</a>
            <a href="{{ route('business.employees.index', ['coverage' => 'inactive']) }}" @if ($filter === 'inactive') data-coverage-active @endif>Inactive / expired</a>
        </nav>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm">
                <h2 class="text-sm font-semibold text-slate-900">Add employee</h2>
                <form method="POST" action="{{ route('business.employees.store') }}" class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                    @csrf
                    <input name="first_name" required value="{{ old('first_name') }}" placeholder="First name" class="rounded-lg border-slate-300 text-sm focus:border-hero-primary focus:ring-hero-primary">
                    <input name="last_name" required value="{{ old('last_name') }}" placeholder="Last name" class="rounded-lg border-slate-300 text-sm focus:border-hero-primary focus:ring-hero-primary">
                    <div class="sm:col-span-2">
                        <label class="text-xs font-medium text-slate-600">Date of birth</label>
                        <input
                            name="date_of_birth"
                            type="date"
                            required
                            max="{{ now()->toDateString() }}"
                            value="{{ old('date_of_birth') }}"
                            class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-hero-primary focus:ring-hero-primary"
                        >
                        @error('date_of_birth')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <input name="email" type="email" value="{{ old('email') }}" placeholder="Email (optional)" class="rounded-lg border-slate-300 text-sm focus:border-hero-primary focus:ring-hero-primary sm:col-span-2">
                    <input name="phone" value="{{ old('phone') }}" placeholder="Phone (optional)" class="rounded-lg border-slate-300 text-sm focus:border-hero-primary focus:ring-hero-primary sm:col-span-2">
                    <div class="sm:col-span-2">
                        <label class="text-xs font-medium text-slate-600">Plan</label>
                        <select name="plan_id" required class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-hero-primary focus:ring-hero-primary">
                            @foreach($plans as $plan)
                                <option value="{{ $plan->id }}" @selected((string) old('plan_id') === (string) $plan->id)>{{ $plan->name }} ({{ $plan->code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <button type="submit" class="rounded-lg bg-hero-primary px-4 py-2 text-sm font-semibold text-white transition hover:bg-hero-primary-hover">Add employee</button>
                    </div>
                </form>
            </div>

            <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm">
                <h2 class="text-sm font-semibold text-slate-900">Upload employee list (CSV)</h2>
                <p class="mt-1 text-xs text-slate-500">Required columns: <span class="font-mono">first_name</span>, <span class="font-mono">last_name</span>. Optional: <span class="font-mono">date_of_birth</span> (or <span class="font-mono">dob</span>), <span class="font-mono">email</span>, <span class="font-mono">phone</span>, <span class="font-mono">plan_code</span> or <span class="font-mono">plan_id</span>. Set a <strong>default plan</strong> under Company billing if rows omit plan.</p>
                <form method="POST" action="{{ route('business.employees.import') }}" enctype="multipart/form-data" class="mt-4 space-y-3">
                    @csrf
                    <input type="file" name="file" accept=".csv,.txt" required class="block w-full text-sm text-slate-600">
                    @error('file')
                        <p class="text-xs text-red-600">{{ $message }}</p>
                    @enderror
                    <button type="submit" class="rounded-lg bg-hero-primary px-4 py-2 text-sm font-semibold text-white transition hover:bg-hero-primary-hover">Import CSV</button>
                </form>
            </div>
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-4">
                <h2 class="text-sm font-semibold text-slate-900">Roster</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Employee</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Plan</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Visitors</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-600">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($employees as $m)
                            @php
                                $mem = $m->members->firstWhere('is_primary', true) ?? $m->members->first();
                                $visitors = $m->dependents->where('relationship', 'visitor');
                            @endphp
                            <tr class="align-top">
                                <td class="px-4 py-3">
                                    <div class="font-medium text-slate-900">{{ $mem ? $mem->first_name.' '.$mem->last_name : '—' }}</div>
                                    <div class="text-xs text-slate-500">{{ $mem?->email ?? '—' }}</div>
                                    <div class="text-xs text-slate-500">DOB: {{ $mem?->date_of_birth?->format('Y-m-d') ?? '—' }}</div>
                                    <div class="font-mono text-xs text-slate-400">{{ $m->membership_number }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <form method="POST" action="{{ route('business.employees.plan', $m) }}" class="flex flex-col gap-1">
                                        @csrf
                                        @method('PATCH')
                                        <select name="plan_id" class="max-w-[14rem] rounded-lg border-slate-300 text-xs" onchange="this.form.submit()">
                                            @foreach($plans as $plan)
                                                <option value="{{ $plan->id }}" @selected($m->plan_id === $plan->id)>{{ $plan->name }}</option>
                                            @endforeach
                                        </select>
                                    </form>
                                </td>
                                <td class="px-4 py-3">
                                    <form method="POST" action="{{ route('business.employees.status', $m) }}">
                                        @csrf
                                        @method('PATCH')
                                        <select name="status" class="rounded-lg border-slate-300 text-xs" onchange="this.form.submit()">
                                            @foreach(['active','inactive','expired','cancelled'] as $st)
                                                <option value="{{ $st }}" @selected($m->status === $st)>{{ ucfirst($st) }}</option>
                                            @endforeach
                                        </select>
                                    </form>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="text-xs text-slate-600">{{ $visitors->count() }} temporary</div>
                                    @foreach($visitors as $v)
                                        <div class="mt-1 flex items-center justify-between gap-2 text-xs">
                                            <span>{{ $v->first_name }} {{ $v->last_name }}</span>
                                            <form method="POST" action="{{ route('business.visitors.destroy', $v) }}" data-swal-confirm="Remove this visitor from temporary coverage?" data-swal-title="Remove visitor?" data-swal-confirm-text="Remove visitor" data-swal-cancel-text="Cancel">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:underline">Remove</button>
                                            </form>
                                        </div>
                                    @endforeach
                                    <form method="POST" action="{{ route('business.visitors.store', $m) }}" class="mt-2 grid grid-cols-1 gap-1 border-t border-slate-100 pt-2">
                                        @csrf
                                        <input name="first_name" required placeholder="Visitor first" class="rounded border-slate-200 text-xs">
                                        <input name="last_name" required placeholder="Visitor last" class="rounded border-slate-200 text-xs">
                                        <input name="phone" placeholder="Phone" class="rounded border-slate-200 text-xs">
                                        <button type="submit" class="rounded bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-800">Add visitor</button>
                                    </form>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <form method="POST" action="{{ route('business.employees.destroy', $m) }}" data-swal-confirm="This will remove the employee and their membership coverage for your company." data-swal-title="Remove employee?" data-swal-confirm-text="Remove employee" data-swal-cancel-text="Cancel">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs font-semibold text-red-700 hover:underline">Remove</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-slate-500">No employees match this filter.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-portal-layout>
