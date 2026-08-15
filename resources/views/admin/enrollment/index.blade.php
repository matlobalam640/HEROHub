<x-portal-layout>
    <div class="space-y-6">
        <div class="hero-dispatch-hero">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <div class="hero-dispatch-hero__eyebrow">Dispatch · Walk-in member</div>
                    <h1 class="hero-dispatch-hero__title">Enroll walk-in member</h1>
                    <p class="hero-dispatch-hero__lead">Add members who visit the office in person. Collect payment with USA Payments on this device, or activate immediately for cash/check/comp memberships.</p>
                </div>
                <div class="hidden shrink-0 sm:block" aria-hidden="true">
                    <span class="hero-dispatch-hero__icon">
                        <i class="fa-solid fa-id-card" aria-hidden="true"></i>
                    </span>
                </div>
            </div>
        </div>

        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
                {{ session('status') }}
            </div>
        @endif

        @if ($enrollmentResult)
            <div class="hero-portal-panel overflow-hidden">
                <div class="hero-panel-header border-b border-slate-100 px-6 py-4">
                    <div class="text-sm font-semibold text-slate-900">Enrollment complete</div>
                </div>
                <div class="space-y-3 p-6 text-sm text-slate-700">
                    <p><strong>{{ $enrollmentResult['member_name'] }}</strong> — {{ $enrollmentResult['email'] }}</p>
                    <p>Membership #: <span class="font-mono">{{ $enrollmentResult['membership_number'] }}</span></p>
                    <p>Payment: {{ $enrollmentResult['payment_method'] === 'usa_payments' ? 'USA Payments (card)' : 'Manual / cash / check' }}</p>
                    <a href="{{ route('portal.membership.show', ['membership' => $enrollmentResult['membership_id']]) }}"
                       class="inline-flex items-center gap-2 text-sm font-semibold text-hero-primary hover:underline">
                        Open membership record →
                    </a>
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

        @if ($plans->isEmpty())
            <div class="hero-portal-panel p-6 text-sm text-amber-900">
                No active retail plans are available. Activate retail catalog plans before enrolling walk-in members.
            </div>
        @else
            <div class="hero-portal-panel overflow-hidden">
                <div class="hero-panel-header border-b border-slate-100 px-6 py-4">
                    <div class="text-sm font-semibold text-slate-900">Member details</div>
                </div>
                <form method="POST" action="{{ route('admin.enrollment.store') }}" class="grid grid-cols-1 gap-4 p-6 sm:grid-cols-2">
                    @csrf

                    <div class="sm:col-span-2">
                        <label for="plan_id" class="block text-sm font-medium text-slate-700">Retail plan</label>
                        <select id="plan_id" name="plan_id" required class="mt-2 w-full rounded-xl border border-slate-200 text-sm focus:border-hero-primary focus:ring-hero-primary">
                            @foreach ($plans as $plan)
                                <option value="{{ $plan->id }}" @selected(old('plan_id') == $plan->id)>
                                    {{ $plan->name }} ({{ $plan->code }})
                                    @if ($plan->price !== null) — ${{ number_format((float) $plan->price, 2) }} @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="sm:col-span-2">
                        <label for="interval" class="block text-sm font-medium text-slate-700">Billing interval</label>
                        <select id="interval" name="interval" class="mt-2 w-full rounded-xl border border-slate-200 text-sm focus:border-hero-primary focus:ring-hero-primary">
                            <option value="yearly" @selected(old('interval', 'yearly') === 'yearly')>Annual</option>
                            <option value="monthly" @selected(old('interval') === 'monthly')>Monthly</option>
                            <option value="onetime" @selected(old('interval') === 'onetime')>One-time</option>
                        </select>
                        <p class="mt-1 text-xs text-slate-500">Used for USA Payments pricing. Manual enrollments use plan defaults when interval does not apply.</p>
                    </div>

                    <div>
                        <label for="first_name" class="block text-sm font-medium text-slate-700">First name</label>
                        <input id="first_name" name="first_name" value="{{ old('first_name') }}" required
                               class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-hero-primary focus:ring-hero-primary">
                    </div>
                    <div>
                        <label for="last_name" class="block text-sm font-medium text-slate-700">Last name</label>
                        <input id="last_name" name="last_name" value="{{ old('last_name') }}" required
                               class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-hero-primary focus:ring-hero-primary">
                    </div>
                    <div class="sm:col-span-2">
                        <label for="email" class="block text-sm font-medium text-slate-700">Email (portal login)</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" required
                               class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-hero-primary focus:ring-hero-primary">
                        <p class="mt-1 text-xs text-slate-500">New accounts should use <strong>Forgot password</strong> on the login page to set a password.</p>
                    </div>
                    <div class="sm:col-span-2">
                        <label for="phone" class="block text-sm font-medium text-slate-700">Phone (optional)</label>
                        <input id="phone" name="phone" value="{{ old('phone') }}"
                               class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-hero-primary focus:ring-hero-primary">
                    </div>

                    <div class="sm:col-span-2">
                        <fieldset>
                            <legend class="text-sm font-medium text-slate-700">Payment</legend>
                            <div class="mt-3 space-y-2">
                                @if ($usaPaymentsEnabled)
                                    <label class="flex items-start gap-3 rounded-xl border border-slate-200 px-4 py-3">
                                        <input type="radio" name="payment_method" value="usa_payments" @checked(old('payment_method', 'usa_payments') === 'usa_payments')
                                               class="mt-1 text-hero-primary focus:ring-hero-primary">
                                        <span>
                                            <span class="block text-sm font-semibold text-slate-900">Collect card payment (USA Payments)</span>
                                            <span class="block text-xs text-slate-500">Creates a pending membership, then opens the secure card form on this device.</span>
                                        </span>
                                    </label>
                                @endif
                                <label class="flex items-start gap-3 rounded-xl border border-slate-200 px-4 py-3">
                                    <input type="radio" name="payment_method" value="manual" @checked(old('payment_method') === 'manual' || ! $usaPaymentsEnabled)
                                           class="mt-1 text-hero-primary focus:ring-hero-primary">
                                    <span>
                                        <span class="block text-sm font-semibold text-slate-900">Manual / cash / check</span>
                                        <span class="block text-xs text-slate-500">Activates coverage immediately without charging a card online.</span>
                                    </span>
                                </label>
                            </div>
                        </fieldset>
                        @unless ($usaPaymentsEnabled)
                            <p class="mt-2 text-xs text-amber-700">USA Payments keys are not configured — only manual enrollment is available.</p>
                        @endunless
                    </div>

                    <div class="sm:col-span-2 flex flex-wrap gap-3">
                        <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-hero-primary px-5 py-2.5 text-sm font-semibold text-white shadow-hero-cta hover:bg-hero-primary-hover">
                            Continue
                        </button>
                    </div>
                </form>
            </div>
        @endif
    </div>
</x-portal-layout>
