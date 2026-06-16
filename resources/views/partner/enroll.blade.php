<x-portal-layout>
    <div class="w-full max-w-none space-y-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <div class="text-sm font-medium text-hero-primary">Partner / Reseller</div>
                <h1 class="font-display mt-1 text-2xl font-semibold tracking-tight text-slate-900">Enroll new member</h1>
                <p class="mt-1 text-sm text-slate-600">Retail plans only. A customer account is created (or linked) so the member can sign in and use <strong>My membership</strong>.</p>
            </div>
            <a href="{{ route('partner.portal') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 shadow-sm hover:border-hero-primary">← Portal</a>
        </div>

        @if ($plans->isEmpty())
            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-6 text-slate-900">
                <p class="font-semibold">No active retail plans</p>
                <p class="mt-1 text-sm text-slate-700">An administrator must activate retail catalog plans before you can enroll members.</p>
            </div>
        @else
            <div class="max-w-3xl overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
                <form method="POST" action="{{ route('partner.enroll.store') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    @csrf
                    <div class="sm:col-span-2">
                        <label class="text-xs font-medium text-slate-600">Retail plan</label>
                        <select name="plan_id" required class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-hero-primary focus:ring-hero-primary">
                            @foreach($plans as $plan)
                                <option value="{{ $plan->id }}" @selected(old('plan_id') == $plan->id)>
                                    {{ $plan->name }} ({{ $plan->code }}) @if($plan->price !== null) — ${{ number_format((float) $plan->price, 2) }} @endif
                                </option>
                            @endforeach
                        </select>
                        @error('plan_id')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="text-xs font-medium text-slate-600">First name</label>
                        <input name="first_name" value="{{ old('first_name') }}" required class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-hero-primary focus:ring-hero-primary">
                        @error('first_name')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="text-xs font-medium text-slate-600">Last name</label>
                        <input name="last_name" value="{{ old('last_name') }}" required class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-hero-primary focus:ring-hero-primary">
                        @error('last_name')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label class="text-xs font-medium text-slate-600">Email (login)</label>
                        <input name="email" type="email" value="{{ old('email') }}" required autocomplete="email" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-hero-primary focus:ring-hero-primary">
                        @error('email')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-slate-500">New accounts should use <strong>Forgot password</strong> on the login page to set a password.</p>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="text-xs font-medium text-slate-600">Phone (optional)</label>
                        <input name="phone" value="{{ old('phone') }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-hero-primary focus:ring-hero-primary">
                        @error('phone')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="sm:col-span-2 flex flex-wrap gap-3">
                        <button type="submit" class="rounded-lg bg-hero-primary px-4 py-2 text-sm font-semibold text-white">Complete enrollment</button>
                        <a href="{{ route('partner.portal') }}" class="inline-flex items-center rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700">Cancel</a>
                    </div>
                </form>
            </div>
        @endif
    </div>
</x-portal-layout>
