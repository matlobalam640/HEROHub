<x-portal-layout>
    <div class="w-full max-w-none space-y-6">
        <div>
            @include('customer.membership.partials.portal-eyebrow')
            <h1 class="font-display mt-1 text-2xl font-semibold tracking-tight text-slate-900">My Membership</h1>
        </div>

        @if (session('status'))
            <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                {{ session('status') }}
            </div>
        @endif

            @if(!$membership)
                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="p-6 text-slate-900">
                        <div class="text-lg font-semibold">No membership found</div>
                        <p class="mt-1 text-slate-600">Once a membership is assigned to your account, it will appear here.</p>
                        <div class="mt-5 rounded-xl border border-slate-100 bg-slate-50 p-4 text-sm text-slate-600">
                            <p class="font-semibold text-slate-800">Try demo data</p>
                            <p class="mt-1">Sign in as <span class="font-mono text-xs font-semibold text-slate-800">customer1@demo.herohub.local</span> (password <span class="font-mono text-xs">password</span>) — that account already has a membership after seeding.</p>
                            <p class="mt-3">If you use <span class="font-mono text-xs font-semibold">admin@demo.herohub.local</span> or <span class="font-mono text-xs font-semibold">business@demo.herohub.local</span>, run <span class="font-mono text-xs">php artisan db:seed</span> so <span class="font-mono text-xs">BindDemoMembershipSeeder</span> can link a demo personal membership to that login.</p>
                            <p class="mt-2">Or set <span class="font-mono text-xs">HERO_BIND_MEMBERSHIP_EMAIL</span> in <span class="font-mono text-xs">.env</span> to your email and run <span class="font-mono text-xs">php artisan db:seed --class=BindDemoMembershipSeeder</span>.</p>
                            @if(auth()->user()->hasRole('business'))
                                <p class="mt-3 text-slate-700">For company HR: your <strong>My membership</strong> card reflects a membership tied to <em>this</em> login (<span class="font-mono text-xs">account_user_id</span>), separate from employees you manage under Company / HR.</p>
                            @endif
                        </div>
                    </div>
                </div>
            @else
                @php
                    $primary = $membership->members->firstWhere('is_primary', true) ?? $membership->members->first();
                @endphp

                @if($card)
                    <div id="digital-membership-card" class="scroll-mt-28 overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-md shadow-slate-200/50 ring-1 ring-slate-100">
                        <div class="hero-panel-header border-b border-slate-100 bg-gradient-to-r from-slate-50 to-[color:var(--dashboard-gold-soft)] px-6 py-4">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <div class="text-sm font-semibold uppercase tracking-wide text-slate-900">Digital membership card</div>
                                    <div class="text-xs text-slate-500">Save or print — QR encodes your membership reference</div>
                                </div>
                                <a href="{{ route('customer.membership.card-pdf') }}"
                                   class="inline-flex shrink-0 items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-700 shadow-sm transition hover:border-hero-primary hover:text-hero-primary">
                                    <i class="fa-solid fa-file-pdf text-red-600" aria-hidden="true"></i>
                                    Download PDF
                                </a>
                            </div>
                        </div>
                        <div class="p-6 text-slate-900">
                            <div class="max-w-md w-full">
                                <x-membership-digital-card :card="$card" compact />
                            </div>

                            @include('customer.membership.partials.auto-renew-panel')
                        </div>
                    </div>
                @else
                    <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-md shadow-slate-200/50 ring-1 ring-slate-100">
                        <div class="hero-panel-header border-b border-slate-100 bg-gradient-to-r from-slate-50 to-[color:var(--dashboard-gold-soft)] px-6 py-4">
                            <div class="text-sm font-semibold text-slate-900">Membership details</div>
                            <div class="text-xs text-slate-500">Linked to your login</div>
                        </div>
                        <div class="p-6 text-slate-900">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <div class="text-sm text-slate-600">Membership ID</div>
                                    <div class="font-mono text-lg">{{ $membership->membership_number }}</div>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm text-slate-600">Status</div>
                                    <div class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold
                                        @class([
                                            'bg-green-50 text-green-700' => $membership->status === 'active',
                                            'bg-slate-100 text-slate-700' => $membership->status !== 'active',
                                        ])
                                    ">
                                        {{ ucfirst($membership->status) }}
                                    </div>
                                </div>
                            </div>

                            <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <div class="text-sm text-slate-600">Plan</div>
                                    <div class="font-medium">{{ $membership->plan?->name ?? '—' }}</div>
                                </div>
                                <div>
                                    <div class="text-sm text-slate-600">Coverage</div>
                                    <div class="font-medium">
                                        {{ $membership->coverage_starts_on?->format('Y-m-d') ?? '—' }}
                                        →
                                        {{ $membership->coverage_ends_on?->format('Y-m-d') ?? '—' }}
                                    </div>
                                </div>
                            </div>

                            <div class="mt-6">
                                <div class="text-sm text-slate-600">Primary member</div>
                                <div class="font-medium">
                                    {{ $primary ? $primary->first_name.' '.$primary->last_name : '—' }}
                                </div>
                                @if($primary)
                                    <div class="text-sm text-slate-600">{{ $primary->phone }}</div>
                                @endif
                            </div>

                            @include('customer.membership.partials.auto-renew-panel')
                        </div>
                    </div>
                @endif
            @endif
    </div>
</x-portal-layout>
