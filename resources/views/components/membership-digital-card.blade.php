@props([
    'card' => [],
    'compact' => false,
])

@php
    $companyName = $card['companyName'] ?? '';
    $memberName = $card['memberName'] ?? '—';
    $membershipType = $card['membershipType'] ?? '—';
    $membershipId = $card['membershipId'] ?? '—';
    $expiration = $card['expiration'] ?? '—';
    $status = $card['status'] ?? '—';
    $qrDataUri = $card['qrDataUri'] ?? '';
@endphp

<div {{ $attributes->class([
    'membership-digital-card rounded-[1.35rem] bg-[#e4e4e8] shadow-[0_12px_40px_-12px_rgba(15,23,42,0.35)] ring-1 ring-slate-900/10',
    'p-5' => $compact,
    'p-8' => ! $compact,
]) }}>
    <div class="flex items-start justify-between gap-3 sm:gap-4">
        <div class="flex min-w-0 flex-1 items-center gap-2 sm:gap-3">
            <img src="{{ asset('brand/hero-logo.png') }}" alt="" @class([
                'w-auto shrink-0 object-contain',
                'h-9' => $compact,
                'h-11' => ! $compact,
            ]) width="44" height="44">
            <div @class([
                'min-w-0 font-display font-bold leading-snug text-slate-950',
                'text-sm sm:text-base' => $compact,
                'text-base sm:text-lg' => ! $compact,
            ])>
                {{ $companyName }}
            </div>
        </div>
        <div class="shrink-0 text-right">
            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-700">Status</div>
            <div @class([
                'mt-0.5 font-bold text-slate-950',
                'text-xs' => $compact,
                'text-sm' => ! $compact,
            ])>{{ $status }}</div>
        </div>
    </div>

    <div @class(['mt-5' => $compact, 'mt-8' => ! $compact])>
        <div class="text-[10px] font-bold uppercase tracking-wider text-slate-700">Member's name</div>
        <div @class([
            'mt-1 font-display font-bold leading-tight tracking-tight text-slate-950',
            'text-2xl sm:text-3xl' => $compact,
            'text-3xl sm:text-4xl' => ! $compact,
        ])>
            {{ $memberName }}
        </div>
    </div>

    <div @class([
        'grid grid-cols-1 sm:grid-cols-2',
        'mt-5 gap-4 sm:gap-5' => $compact,
        'mt-8 gap-6 sm:gap-8' => ! $compact,
    ])>
        <div class="min-w-0">
            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-700">Membership type</div>
            <div @class([
                'mt-1 font-medium leading-snug text-slate-900',
                'text-xs' => $compact,
                'text-sm' => ! $compact,
            ])>{{ $membershipType }}</div>
        </div>
        <div class="min-w-0 sm:text-right">
            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-700 sm:ml-auto">Membership ID</div>
            <div @class([
                'mt-1 font-mono font-bold text-slate-950',
                'text-base' => $compact,
                'text-lg' => ! $compact,
            ])>{{ $membershipId }}</div>
        </div>
    </div>

    <div @class(['mt-4' => $compact, 'mt-6' => ! $compact])>
        <div class="text-[10px] font-bold uppercase tracking-wider text-slate-700">Expiration</div>
        <div @class([
            'mt-1 font-semibold text-slate-950',
            'text-sm' => $compact,
            'text-base' => ! $compact,
        ])>{{ $expiration }}</div>
    </div>

    @if($qrDataUri !== '')
        <div @class(['mt-5 flex justify-center' => $compact, 'mt-8 flex justify-center' => ! $compact])>
            <div @class([
                'rounded-xl bg-white shadow-inner ring-1 ring-slate-200',
                'p-2' => $compact,
                'p-3' => ! $compact,
            ])>
                <img src="{{ $qrDataUri }}" alt="Membership verification QR code" @class([
                    'h-32 w-32 sm:h-36 sm:w-36' => $compact,
                    'h-44 w-44 sm:h-52 sm:w-52' => ! $compact,
                ]) width="208" height="208">
            </div>
        </div>
    @endif
</div>
