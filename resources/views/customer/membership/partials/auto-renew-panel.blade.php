<div id="auto-renew" class="scroll-mt-28 mt-8 border-t border-slate-200 pt-6">
    <h2 class="text-sm font-semibold text-slate-900">Auto-renewal</h2>
    <div class="mt-3 flex flex-col gap-3 sm:flex-row sm:items-stretch sm:justify-between">
        @if($membership->auto_renew)
            <div role="alert" class="flex flex-1 items-start gap-3 rounded-xl border border-slate-200 bg-hero-primary-soft px-4 py-3 text-sm text-hero-primary shadow-sm ring-1 ring-slate-200/70">
                <span class="mt-0.5 inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white text-hero-primary shadow-sm ring-1 ring-slate-200/80" aria-hidden="true">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </span>
                <p class="pt-0.5 leading-snug">
                    Auto-renew is currently <span class="font-semibold">enabled</span>.
                </p>
            </div>
        @else
            <div role="alert" class="flex flex-1 items-start gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-950 shadow-sm ring-1 ring-red-100">
                <span class="mt-0.5 inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-red-100 text-red-700" aria-hidden="true">
                    <i class="fa-solid fa-circle-exclamation"></i>
                </span>
                <p class="pt-0.5 leading-snug">
                    Auto-renew is currently <span class="font-semibold">disabled</span>.
                </p>
            </div>
        @endif
        <form method="POST" action="{{ route('customer.membership.auto-renew.update') }}" class="flex shrink-0 items-center sm:justify-end">
            @csrf
            <input type="hidden" name="auto_renew" value="{{ $membership->auto_renew ? 0 : 1 }}">
            <button type="submit" class="w-full rounded-full bg-hero-primary px-4 py-2.5 text-sm font-semibold text-white shadow-hero-cta transition hover:bg-hero-primary-hover sm:w-auto">
                {{ $membership->auto_renew ? 'Disable' : 'Enable' }}
            </button>
        </form>
    </div>
</div>
