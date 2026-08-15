<x-portal-layout>
    <div class="space-y-6">
        <div class="hero-dispatch-hero">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <div class="hero-dispatch-hero__eyebrow">Admin · Data migration</div>
                    <h1 class="hero-dispatch-hero__title">Membership CSV import</h1>
                    <p class="hero-dispatch-hero__lead">Upload legacy member rows to create portal accounts, memberships, and primary member profiles. Blank membership numbers auto-generate as <span class="font-mono">HERO-IMP-{{ date('Y') }}-XXXXXX</span>.</p>
                </div>
                <div class="hidden shrink-0 sm:block" aria-hidden="true">
                    <span class="hero-dispatch-hero__icon">
                        <i class="fa-solid fa-file-import" aria-hidden="true"></i>
                    </span>
                </div>
            </div>
        </div>

        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
                {{ session('status') }}
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

        @if ($importResult)
            <div class="hero-portal-panel overflow-hidden">
                <div class="hero-panel-header border-b border-slate-100 px-6 py-4">
                    <div class="text-sm font-semibold text-slate-900">Import summary</div>
                </div>
                <div class="grid gap-4 p-6 sm:grid-cols-2 lg:grid-cols-5">
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Users created</div>
                        <div class="mt-1 text-2xl font-bold text-slate-900">{{ $importResult['created_users'] ?? 0 }}</div>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Companies created</div>
                        <div class="mt-1 text-2xl font-bold text-slate-900">{{ $importResult['created_companies'] ?? 0 }}</div>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Memberships created</div>
                        <div class="mt-1 text-2xl font-bold text-slate-900">{{ $importResult['created_memberships'] ?? 0 }}</div>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Skipped</div>
                        <div class="mt-1 text-2xl font-bold text-slate-900">{{ $importResult['skipped'] ?? 0 }}</div>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Errors</div>
                        <div class="mt-1 text-2xl font-bold text-red-700">{{ $importResult['errors'] ?? 0 }}</div>
                    </div>
                </div>
                @if (! empty($importResult['messages']))
                    <div class="border-t border-slate-100 px-6 py-4">
                        <div class="max-h-48 overflow-y-auto font-mono text-xs text-slate-700">
                            @foreach ($importResult['messages'] as $message)
                                <div>{{ $message }}</div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        @endif

        <div class="grid gap-6 lg:grid-cols-2">
            <div class="hero-portal-panel overflow-hidden">
                <div class="hero-panel-header border-b border-slate-100 px-6 py-4">
                    <div class="text-sm font-semibold text-slate-900">1. Download template</div>
                </div>
                <div class="space-y-4 p-6 text-sm text-slate-700">
                    <p>Use the sample CSV with the exact column headers the importer expects. Required columns: <strong>record_type, first_name, last_name, plan_code</strong>.</p>
                    <p>Set <strong>record_type</strong> to <span class="font-mono">b2c</span> (individual customer), <span class="font-mono">b2b_company</span> (creates company + HR owner), or <span class="font-mono">b2b_employee</span> (employee under a company). Email is required for <span class="font-mono">b2c</span> and <span class="font-mono">b2b_company</span>; <strong>company_name</strong> is required for both business types.</p>
                    <p>Leave <strong>membership_number</strong> blank to auto-generate <span class="font-mono">HERO-IMP-{{ date('Y') }}-000001</span> style IDs. Provide a value only when preserving a legacy membership ID.</p>
                    <p>B2B companies appear under <a href="{{ route('admin.companies.index') }}" class="font-semibold text-hero-primary hover:underline">Companies</a>. HR users can import employees from the company portal using the same employee CSV layout.</p>
                    <a href="{{ route('admin.migration.template') }}"
                       class="inline-flex items-center gap-2 rounded-xl bg-hero-primary px-4 py-2.5 text-sm font-semibold text-white shadow-hero-cta hover:bg-hero-primary-hover">
                        <i class="fa-solid fa-download" aria-hidden="true"></i>
                        Download sample CSV
                    </a>
                </div>
            </div>

            <div class="hero-portal-panel overflow-hidden">
                <div class="hero-panel-header border-b border-slate-100 px-6 py-4">
                    <div class="text-sm font-semibold text-slate-900">Plan codes</div>
                </div>
                <div class="p-6">
                    <div class="flex flex-wrap gap-2">
                        @forelse ($planCodes as $code)
                            <span class="rounded-lg bg-slate-100 px-2.5 py-1 font-mono text-xs font-semibold text-slate-800">{{ $code }}</span>
                        @empty
                            <span class="text-sm text-slate-600">No active plans found.</span>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="hero-portal-panel overflow-hidden">
            <div class="hero-panel-header border-b border-slate-100 px-6 py-4">
                <div class="text-sm font-semibold text-slate-900">2. Upload CSV</div>
            </div>
            <form method="POST" action="{{ route('admin.migration.preview') }}" enctype="multipart/form-data" class="space-y-4 p-6">
                @csrf
                <div>
                    <label for="file" class="block text-sm font-medium text-slate-700">CSV file</label>
                    <input id="file" name="file" type="file" accept=".csv,text/csv,text/plain" required
                           class="mt-2 block w-full rounded-xl border border-slate-200 text-sm file:mr-4 file:rounded-lg file:border-0 file:bg-slate-100 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-slate-700">
                    @if ($uploadFilename)
                        <p class="mt-2 text-xs text-slate-500">Loaded file: {{ $uploadFilename }}</p>
                    @endif
                </div>
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-hero-primary px-4 py-2.5 text-sm font-semibold text-white shadow-hero-cta hover:bg-hero-primary-hover">
                    <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                    Preview import
                </button>
            </form>
        </div>

        @if ($preview)
            <div class="hero-portal-panel overflow-hidden">
                <div class="hero-panel-header flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-6 py-4">
                    <div>
                        <div class="text-sm font-semibold text-slate-900">3. Preview</div>
                        <p class="mt-1 text-xs text-slate-600">
                            {{ count($preview['rows']) }} rows —
                            <span class="text-emerald-700">{{ $preview['summary']['valid'] }} valid</span>,
                            <span class="text-amber-700">{{ $preview['summary']['warn'] }} warnings</span>,
                            <span class="text-red-700">{{ $preview['summary']['error'] }} errors</span>
                        </p>
                    </div>
                    <form method="POST" action="{{ route('admin.migration.reset') }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-sm font-semibold text-slate-600 hover:text-slate-900">Clear preview</button>
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">
                            <tr>
                                <th class="px-4 py-3">Line</th>
                                <th class="px-4 py-3">Type</th>
                                <th class="px-4 py-3">Email</th>
                                <th class="px-4 py-3">Name</th>
                                <th class="px-4 py-3">Company</th>
                                <th class="px-4 py-3">Plan</th>
                                <th class="px-4 py-3">Membership #</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3">Checks</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @foreach ($preview['rows'] as $row)
                                @php
                                    $badge = match ($row['level']) {
                                        'valid' => 'bg-emerald-100 text-emerald-800',
                                        'warn' => 'bg-amber-100 text-amber-900',
                                        default => 'bg-red-100 text-red-800',
                                    };
                                @endphp
                                <tr>
                                    <td class="px-4 py-3 font-mono text-xs">{{ $row['line'] }}</td>
                                    <td class="px-4 py-3 font-mono text-xs">{{ $row['record_type'] }}</td>
                                    <td class="px-4 py-3">{{ $row['email'] ?: '—' }}</td>
                                    <td class="px-4 py-3">{{ $row['first_name'] }} {{ $row['last_name'] }}</td>
                                    <td class="px-4 py-3">{{ $row['company_name'] ?: '—' }}</td>
                                    <td class="px-4 py-3 font-mono text-xs">{{ $row['plan_code'] }}</td>
                                    <td class="px-4 py-3 font-mono text-xs">{{ $row['resolved_membership_number'] ?: 'auto' }}</td>
                                    <td class="px-4 py-3">{{ $row['resolved_status'] }}</td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold {{ $badge }}">{{ $row['level'] }}</span>
                                        @if (! empty($row['issues']))
                                            <ul class="mt-2 list-disc space-y-1 pl-4 text-xs text-slate-600">
                                                @foreach ($row['issues'] as $issue)
                                                    <li>{{ $issue }}</li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-slate-100 p-6">
                    <form method="POST" action="{{ route('admin.migration.import') }}" class="space-y-4">
                        @csrf
                        <label class="flex items-center gap-2 text-sm text-slate-700">
                            <input type="checkbox" name="update_existing" value="1" class="rounded border-slate-300 text-hero-primary focus:ring-hero-primary">
                            Update existing memberships when membership_number matches
                        </label>
                        <label class="flex items-center gap-2 text-sm text-slate-700">
                            <input type="checkbox" name="dry_run" value="1" class="rounded border-slate-300 text-hero-primary focus:ring-hero-primary">
                            Dry run only (validate without saving)
                        </label>
                        <div class="flex flex-wrap gap-3">
                            <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-hero-primary px-5 py-2.5 text-sm font-semibold text-white shadow-hero-cta hover:bg-hero-primary-hover">
                                <i class="fa-solid fa-file-import" aria-hidden="true"></i>
                                Run import
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        <div class="hero-portal-panel overflow-hidden">
            <div class="hero-panel-header border-b border-slate-100 px-6 py-4">
                <div class="text-sm font-semibold text-slate-900">Duplicate rules</div>
            </div>
            <ul class="list-disc space-y-2 p-6 pl-10 text-sm text-slate-700">
                <li><strong>b2c</strong> — creates a customer account and individual membership (retail plans only).</li>
                <li><strong>b2b_company</strong> — creates a company record and HR portal owner (business/corporate plans).</li>
                <li><strong>b2b_employee</strong> — creates an employee membership under an existing company (import company rows first).</li>
                <li>Duplicate <strong>email</strong> in the same file is rejected for b2c and b2b_company rows.</li>
                <li>Duplicate <strong>membership_number</strong> in the same file is rejected.</li>
                <li>Rows with an existing <strong>billing_subscription_id</strong> (AWS gateway) are skipped for b2c imports.</li>
                <li><strong>B2B</strong> (<span class="font-mono">b2b_company</span>, <span class="font-mono">b2b_employee</span>) is CSV-only — not synced from AWS. <strong>Walk-in member</strong> is retail (B2C) only.</li>
            </ul>
        </div>
    </div>
</x-portal-layout>
