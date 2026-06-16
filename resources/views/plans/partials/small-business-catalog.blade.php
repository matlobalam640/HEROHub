@props([
    'sections',
    'membershipLabel' => 'Small business membership',
    'plansReturn' => 'small-business',
])

@php
    $navy = 'text-hero-primary';
@endphp

@if ($sections->isEmpty())
    <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50/80 p-8 text-center text-sm text-slate-600">
        <p class="font-medium text-slate-800">No plans available</p>
        <p class="mt-2">Run the small business seeder to load plans from the latest pricing document.</p>
    </div>
@endif

@foreach ($sections as $section)
    <section class="space-y-4">
        <div class="border-t border-slate-200/90 pt-6 first:border-t-0 first:pt-0">
            <h2 class="font-display text-xl font-bold tracking-tight text-slate-900">{{ $section['label'] }}</h2>
            <p class="mt-0.5 text-xs font-medium uppercase tracking-wide text-slate-400">{{ $membershipLabel }}</p>
        </div>

        <div class="grid grid-cols-1 items-stretch gap-4 lg:grid-cols-2 lg:gap-6">
            @foreach ($section['plans'] as $plan)
                @php
                    // Match retail: first card white, second gold shaded, alternating; VIP tier always gold
                    $isGoldCard = (($plan->tier ?? '') === 'vip')
                        || (($plan->tier ?? '') !== 'vip' && $loop->index % 2 === 1);
                @endphp
                <article
                    class="flex h-full min-h-0 w-full flex-col rounded-2xl p-6 shadow-hero-card ring-1 {{ $isGoldCard ? 'ring-amber-900/20' : 'bg-white ring-slate-200/90' }}"
                    @if ($isGoldCard)
                        style="background-image: linear-gradient(180deg, #faf6d4 0%, #f2e088 52%, #dec328 100%);"
                    @endif
                >
                    <div class="flex min-h-0 min-w-0 flex-1 flex-col">
                        <h3 class="font-display text-base font-bold leading-snug sm:text-lg {{ $navy }} break-words">
                            <span aria-hidden="true">→</span>
                            {{ $plan->code }} – {{ $plan->name }}
                        </h3>

                        <ul class="mt-5 space-y-2.5 text-sm leading-relaxed {{ $navy }}">
                            <li class="flex gap-4">
                                <span class="shrink-0 pt-0.5 font-semibold leading-none {{ $navy }}" aria-hidden="true">◆</span>
                                <span class="min-w-0 flex-1 break-words font-bold">{{ $plan->catalogPrimaryPriceLine() }}</span>
                            </li>
                            @if ($plan->min_members)
                                <li class="flex gap-4">
                                    <span class="shrink-0 pt-0.5 font-semibold leading-none {{ $navy }}" aria-hidden="true">◆</span>
                                    <span class="min-w-0 flex-1 break-words">Minimum {{ $plan->min_members }} members required</span>
                                </li>
                            @endif
                            @if (is_array($plan->features) && count($plan->features))
                                @foreach ($plan->features as $line)
                                    <li class="flex gap-4">
                                        <span class="shrink-0 pt-0.5 font-semibold leading-none {{ $navy }}" aria-hidden="true">◆</span>
                                        <span class="min-w-0 flex-1 break-words">{{ $line }}</span>
                                    </li>
                                @endforeach
                            @endif
                        </ul>

                        @if ($plan->ideal_for)
                            <p class="mt-5 text-sm font-normal {{ $navy }}">
                                <span class="font-semibold" aria-hidden="true">→</span>
                                <span class="font-semibold">Ideal for:</span>
                                {{ $plan->ideal_for }}
                            </p>
                        @endif
                    </div>

                    @include('plans.partials.plan-card-stripe-checkout', ['plan' => $plan, 'plansReturn' => $plansReturn])
                </article>
            @endforeach
        </div>
    </section>
@endforeach

@if ($sections->isNotEmpty())
    <p class="mt-10 text-center text-sm italic text-hero-primary">
        Please note: All listed prices are in U.S. Dollars and are subject to a 10% TCA tax.
    </p>
@endif
