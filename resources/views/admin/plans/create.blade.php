<x-portal-layout>
    <div class="mx-auto max-w-5xl space-y-8">
        <div>
            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Admin · {{ $listingTitle }}</div>
            <div class="mt-1 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="text-sm font-medium text-hero-primary">Membership catalog</p>
                    <h1 class="font-display mt-1 text-2xl font-bold tracking-tight text-slate-900">Add membership plan</h1>
                </div>
                <a
                    href="{{ route($backRoute) }}"
                    class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-hero-primary hover:text-hero-primary"
                >
                    ← Back to {{ $listingTitle }}
                </a>
            </div>
            <p class="mt-3 max-w-3xl text-sm leading-relaxed text-slate-600">
                {{ $intro }} Catalog <span class="font-mono text-xs text-slate-800">code</span> must be unique (e.g.
                <span class="font-mono text-xs">HR-01A</span>).
            </p>
        </div>

        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-900">
                {{ session('status') }}
            </div>
        @endif

        <form
            method="post"
            action="{{ route('admin.plans.store') }}"
            class="hero-admin-plan-form overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-hero-card ring-1 ring-slate-200/60"
        >
            @csrf
            <input type="hidden" name="return_listing" value="{{ $returnListing }}" />
            <x-input-error class="px-6 pt-4 sm:px-8" :messages="$errors->get('return_listing')" />

            <div
                class="flex flex-col gap-4 border-b border-slate-200/90 bg-gradient-to-b from-hero-primary-soft/90 via-slate-50/80 to-white px-6 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-8"
            >
                <p class="text-sm font-semibold text-slate-700">
                    Required fields marked with <span class="font-bold text-hero-primary">*</span>
                </p>
                <div class="flex flex-wrap items-center justify-end gap-3 sm:ml-auto">
                    <a
                        href="{{ route($backRoute) }}"
                        class="inline-flex items-center justify-center rounded-xl border-2 border-hero-primary bg-white px-5 py-2.5 text-sm font-semibold text-hero-primary shadow-sm transition hover:bg-hero-primary hover:text-white"
                    >
                        Cancel
                    </a>
                    <button
                        type="submit"
                        class="inline-flex items-center justify-center rounded-xl bg-hero-primary px-5 py-2.5 text-sm font-semibold text-white shadow-hero-cta transition hover:bg-hero-primary-hover focus:outline-none focus-visible:ring-2 focus-visible:ring-hero-primary/40 focus-visible:ring-offset-2"
                    >
                        Save plan
                    </button>
                </div>
            </div>

            <div class="space-y-12 px-6 py-8 sm:px-8">
                {{-- Identity --}}
                <section aria-labelledby="plan-section-identity">
                    <div class="flex items-center gap-2 border-b border-slate-200/80 pb-4">
                        <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-hero-primary" aria-hidden="true"></span>
                        <h2 id="plan-section-identity" class="text-xs font-bold uppercase tracking-[0.12em] text-hero-primary">
                            Plan identity
                        </h2>
                    </div>
                    <div class="hero-admin-plan-grid mt-6">
                        <div class="hero-admin-plan-field sm:col-span-2">
                            <x-input-label for="code" value="Plan code *" />
                            <x-text-input
                                id="code"
                                name="code"
                                type="text"
                                class="block w-full border border-slate-200 px-3 py-2.5 font-mono text-sm"
                                value="{{ old('code') }}"
                                required
                                autocomplete="off"
                            />
                            <x-input-error class="mt-1" :messages="$errors->get('code')" />
                        </div>
                        <div class="hero-admin-plan-field sm:col-span-2">
                            <x-input-label for="name" value="Display name *" />
                            <x-text-input id="name" name="name" type="text" class="block w-full border border-slate-200 px-3 py-2.5" value="{{ old('name') }}" required />
                            <x-input-error class="mt-1" :messages="$errors->get('name')" />
                        </div>
                    </div>
                </section>

                {{-- Classification --}}
                <section aria-labelledby="plan-section-class">
                    <div class="flex items-center gap-2 border-b border-slate-200/80 pb-4">
                        <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-hero-primary" aria-hidden="true"></span>
                        <h2 id="plan-section-class" class="text-xs font-bold uppercase tracking-[0.12em] text-hero-primary">
                            Classification &amp; catalog
                        </h2>
                    </div>
                    <div class="mt-6 space-y-10">
                        <div class="hero-admin-plan-row-pair">
                            <div class="hero-admin-plan-field">
                                <x-input-label for="category" value="Category *" />
                                <select
                                    id="category"
                                    name="category"
                                    class="block w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm shadow-sm focus:border-hero-primary focus:outline-none focus:ring-1 focus:ring-hero-primary"
                                    required
                                >
                                    @foreach (['retail' => 'Retail', 'business' => 'Business', 'corporate' => 'Corporate'] as $val => $label)
                                        <option value="{{ $val }}" @selected(old('category', $defaultCategory) === $val)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <x-input-error class="mt-1" :messages="$errors->get('category')" />
                            </div>
                            <div class="hero-admin-plan-field">
                                <x-input-label for="currency" value="Currency *" />
                                <x-text-input id="currency" name="currency" type="text" class="block w-full border border-slate-200 px-3 py-2.5 uppercase" value="{{ old('currency', 'USD') }}" required maxlength="3" />
                                <x-input-error class="mt-1" :messages="$errors->get('currency')" />
                            </div>
                        </div>
                        <div class="hero-admin-plan-row-pair">
                            <div class="hero-admin-plan-field">
                                <x-input-label for="tier" value="Tier" />
                                <x-text-input id="tier" name="tier" type="text" class="block w-full border border-slate-200 px-3 py-2.5" value="{{ old('tier', $defaultTier ?? '') }}" placeholder="e.g. local, vip" />
                                <x-input-error class="mt-1" :messages="$errors->get('tier')" />
                            </div>
                            <div class="hero-admin-plan-field">
                                <x-input-label for="retail_subgroup" value="Retail subgroup" />
                                <select
                                    id="retail_subgroup"
                                    name="retail_subgroup"
                                    class="block w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm shadow-sm focus:border-hero-primary focus:outline-none focus:ring-1 focus:ring-hero-primary"
                                >
                                    <option value="">— None —</option>
                                    @foreach (['10_day' => '10-Day plans', '1_month' => '1-Month plans', 'annual_individual' => 'Annual — Individual', 'annual_family' => 'Annual — Family'] as $val => $label)
                                        <option value="{{ $val }}" @selected(old('retail_subgroup') === $val)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <x-input-error class="mt-1" :messages="$errors->get('retail_subgroup')" />
                            </div>
                        </div>
                        <div class="hero-admin-plan-row-pair">
                            <div class="hero-admin-plan-field">
                                <x-input-label for="sort_order" value="Sort order" />
                                <x-text-input id="sort_order" name="sort_order" type="number" class="block w-full border border-slate-200 px-3 py-2.5" value="{{ old('sort_order', '0') }}" min="0" />
                                <x-input-error class="mt-1" :messages="$errors->get('sort_order')" />
                            </div>
                            <div class="hero-admin-plan-field">
                                <x-input-label for="coverage_days" value="Coverage (days)" />
                                <x-text-input id="coverage_days" name="coverage_days" type="number" class="block w-full border border-slate-200 px-3 py-2.5" value="{{ old('coverage_days') }}" min="0" />
                                <x-input-error class="mt-1" :messages="$errors->get('coverage_days')" />
                            </div>
                        </div>
                    </div>
                </section>

                {{-- Pricing & billing model --}}
                <section aria-labelledby="plan-section-pricing">
                    <div class="flex items-center gap-2 border-b border-slate-200/80 pb-4">
                        <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-hero-primary" aria-hidden="true"></span>
                        <h2 id="plan-section-pricing" class="text-xs font-bold uppercase tracking-[0.12em] text-hero-primary">
                            Pricing &amp; billing model
                        </h2>
                    </div>
                    <p class="mt-4 text-xs leading-relaxed text-slate-500">
                        List prices power catalog copy and subscribe buttons. Stripe checkout uses these amounts for eligible plans.
                    </p>
                    <div class="mt-6 space-y-10">
                        <div class="hero-admin-plan-row-pair">
                            <div class="hero-admin-plan-field">
                                <x-input-label for="billing_interval" value="Billing interval" />
                                <select
                                    id="billing_interval"
                                    name="billing_interval"
                                    class="block w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm shadow-sm focus:border-hero-primary focus:outline-none focus:ring-1 focus:ring-hero-primary"
                                >
                                    <option value="">— None —</option>
                                    @foreach (['one_time' => 'One-time', 'monthly' => 'Monthly', 'yearly' => 'Yearly'] as $val => $label)
                                        <option value="{{ $val }}" @selected(old('billing_interval') === $val)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <x-input-error class="mt-1" :messages="$errors->get('billing_interval')" />
                            </div>
                            <div class="hero-admin-plan-field">
                                <x-input-label for="commitment_months" value="Commitment (months)" />
                                <x-text-input id="commitment_months" name="commitment_months" type="number" class="block w-full border border-slate-200 px-3 py-2.5" value="{{ old('commitment_months') }}" min="0" />
                                <x-input-error class="mt-1" :messages="$errors->get('commitment_months')" />
                            </div>
                        </div>
                        <div class="hero-admin-plan-row-pair">
                            <div class="hero-admin-plan-field">
                                <x-input-label for="price" value="Price (main)" />
                                <x-text-input id="price" name="price" type="text" inputmode="decimal" class="block w-full border border-slate-200 px-3 py-2.5" value="{{ old('price') }}" placeholder="0.00" />
                                <x-input-error class="mt-1" :messages="$errors->get('price')" />
                            </div>
                            <div class="hero-admin-plan-field">
                                <x-input-label for="price_monthly" value="Price (monthly)" />
                                <x-text-input id="price_monthly" name="price_monthly" type="text" inputmode="decimal" class="block w-full border border-slate-200 px-3 py-2.5" value="{{ old('price_monthly') }}" placeholder="0.00" />
                                <x-input-error class="mt-1" :messages="$errors->get('price_monthly')" />
                            </div>
                        </div>
                    </div>
                </section>

                {{-- Seat / member limits (business & corporate) --}}
                <section aria-labelledby="plan-section-seats">
                    <div class="flex items-center gap-2 border-b border-slate-200/80 pb-4">
                        <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-hero-primary" aria-hidden="true"></span>
                        <h2 id="plan-section-seats" class="text-xs font-bold uppercase tracking-[0.12em] text-hero-primary">
                            Seats &amp; add-ons
                        </h2>
                    </div>
                    <p class="mt-4 text-xs leading-relaxed text-slate-500">Used mainly for business and corporate rows; leave blank for simple retail plans.</p>
                    <div class="mt-6 space-y-10">
                        <div class="hero-admin-plan-row-pair">
                            <div class="hero-admin-plan-field">
                                <x-input-label for="min_members" value="Min members" />
                                <x-text-input id="min_members" name="min_members" type="number" class="block w-full border border-slate-200 px-3 py-2.5" value="{{ old('min_members') }}" min="0" />
                                <x-input-error class="mt-1" :messages="$errors->get('min_members')" />
                            </div>
                            <div class="hero-admin-plan-field">
                                <x-input-label for="max_members" value="Max members" />
                                <x-text-input id="max_members" name="max_members" type="number" class="block w-full border border-slate-200 px-3 py-2.5" value="{{ old('max_members') }}" min="0" />
                                <x-input-error class="mt-1" :messages="$errors->get('max_members')" />
                            </div>
                        </div>
                        <div class="hero-admin-plan-row-pair">
                            <div class="hero-admin-plan-field">
                                <x-input-label for="included_members" value="Included members" />
                                <x-text-input id="included_members" name="included_members" type="number" class="block w-full border border-slate-200 px-3 py-2.5" value="{{ old('included_members') }}" min="0" max="255" />
                                <x-input-error class="mt-1" :messages="$errors->get('included_members')" />
                            </div>
                            <div class="hero-admin-plan-field">
                                <x-input-label for="addon_price_yearly" value="Add-on price (yearly)" />
                                <x-text-input id="addon_price_yearly" name="addon_price_yearly" type="text" inputmode="decimal" class="block w-full border border-slate-200 px-3 py-2.5" value="{{ old('addon_price_yearly') }}" />
                                <x-input-error class="mt-1" :messages="$errors->get('addon_price_yearly')" />
                            </div>
                        </div>
                    </div>
                </section>

                {{-- Marketing --}}
                <section aria-labelledby="plan-section-marketing">
                    <div class="flex items-center gap-2 border-b border-slate-200/80 pb-4">
                        <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-hero-primary" aria-hidden="true"></span>
                        <h2 id="plan-section-marketing" class="text-xs font-bold uppercase tracking-[0.12em] text-hero-primary">
                            Marketing copy
                        </h2>
                    </div>
                    <div class="hero-admin-plan-field mt-6">
                        <x-input-label for="ideal_for" value="Ideal for" />
                        <x-text-input id="ideal_for" name="ideal_for" type="text" class="block w-full border border-slate-200 px-3 py-2.5" value="{{ old('ideal_for') }}" />
                        <x-input-error class="mt-1" :messages="$errors->get('ideal_for')" />
                    </div>
                </section>

                {{-- Zoho product codes (optional — used to match Zoho Billing webhooks to catalog plans) --}}
                <section
                    aria-labelledby="plan-section-zoho-codes"
                    class="rounded-2xl border border-slate-200/90 bg-slate-50/80 p-5 shadow-sm ring-1 ring-slate-100 sm:p-6"
                >
                    <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-200/80 pb-4">
                        <div>
                            <h2 id="plan-section-zoho-codes" class="text-xs font-bold uppercase tracking-[0.12em] text-slate-600">
                                Zoho product codes (optional)
                            </h2>
                            <p class="mt-2 max-w-2xl text-xs leading-relaxed text-slate-600">
                                If you use Zoho Billing webhooks, set the product or plan codes here so incoming payloads map to this catalog row. Public checkout uses Stripe only.
                            </p>
                        </div>
                    </div>

                    <div class="hero-admin-plan-zoho-grid mt-6">
                        <div class="min-w-0 rounded-xl border border-slate-200/90 bg-white/95 p-4 shadow-sm ring-1 ring-slate-100 sm:p-5">
                            <div class="border-b border-slate-100 pb-3">
                                <h3 class="text-sm font-bold text-slate-900">Monthly</h3>
                            </div>
                            <div class="hero-admin-plan-field mt-4">
                                <x-input-label for="zoho_code_monthly" value="Zoho plan / product code" />
                                <x-text-input
                                    id="zoho_code_monthly"
                                    name="zoho_code_monthly"
                                    type="text"
                                    class="block w-full border border-slate-200 px-3 py-2.5 font-mono text-sm"
                                    value="{{ old('zoho_code_monthly') }}"
                                    placeholder="e.g. HB-01-M"
                                    autocomplete="off"
                                />
                                <x-input-error class="mt-1" :messages="$errors->get('zoho_code_monthly')" />
                            </div>
                        </div>

                        <div class="min-w-0 rounded-xl border border-slate-200/90 bg-white/95 p-4 shadow-sm ring-1 ring-slate-100 sm:p-5">
                            <div class="border-b border-slate-100 pb-3">
                                <h3 class="text-sm font-bold text-slate-900">Annual</h3>
                            </div>
                            <div class="hero-admin-plan-field mt-4">
                                <x-input-label for="zoho_code_yearly" value="Zoho plan / product code" />
                                <x-text-input
                                    id="zoho_code_yearly"
                                    name="zoho_code_yearly"
                                    type="text"
                                    class="block w-full border border-slate-200 px-3 py-2.5 font-mono text-sm"
                                    value="{{ old('zoho_code_yearly') }}"
                                    placeholder="e.g. HB-01-Y"
                                    autocomplete="off"
                                />
                                <x-input-error class="mt-1" :messages="$errors->get('zoho_code_yearly')" />
                            </div>
                        </div>
                    </div>
                </section>

                {{-- Features --}}
                <section aria-labelledby="plan-section-features">
                    <div class="flex items-center gap-2 border-b border-slate-200/80 pb-4">
                        <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-hero-primary" aria-hidden="true"></span>
                        <h2 id="plan-section-features" class="text-xs font-bold uppercase tracking-[0.12em] text-hero-primary">
                            Catalog features
                        </h2>
                    </div>
                    <div class="hero-admin-plan-field mt-6">
                        <x-input-label for="features_text" value="Features (one per line)" />
                        <textarea
                            id="features_text"
                            name="features_text"
                            rows="8"
                            class="block w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm shadow-sm placeholder:text-slate-400 focus:border-hero-primary focus:outline-none focus:ring-1 focus:ring-hero-primary"
                        >{{ old('features_text') }}</textarea>
                        <p class="text-xs text-slate-500">Each line becomes a bullet on the public plan card.</p>
                        <x-input-error class="mt-1" :messages="$errors->get('features_text')" />
                    </div>
                </section>

                {{-- Status --}}
                <section aria-labelledby="plan-section-status" class="rounded-xl border border-slate-200/80 bg-slate-50/60 px-4 py-4 sm:px-5">
                    <div class="flex flex-wrap items-center gap-3">
                        <input
                            id="active"
                            name="active"
                            type="checkbox"
                            value="1"
                            class="h-4 w-4 rounded border-slate-300 text-hero-primary focus:ring-hero-primary"
                            @checked(session()->hasOldInput() ? (bool) old('active') : true)
                        />
                        <x-input-label for="active" value="Plan is active (visible for new memberships)" class="!mb-0 font-medium" />
                    </div>
                </section>
            </div>
        </form>
    </div>
</x-portal-layout>
