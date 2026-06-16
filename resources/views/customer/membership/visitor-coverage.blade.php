<x-portal-layout>
    <div class="w-full max-w-none space-y-6">
        <div>
            @include('customer.membership.partials.portal-eyebrow')
            <h1 class="font-display mt-1 text-2xl font-semibold tracking-tight text-slate-900">Visitor coverage</h1>
            <p class="mt-1 text-sm text-slate-600">Add or remove short-term visitors on your membership.</p>
        </div>

        @if (session('status'))
            <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                {{ session('status') }}
            </div>
        @endif

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2 overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-md shadow-slate-200/50 ring-1 ring-slate-100">
                <div class="hero-panel-header border-b border-slate-100 bg-gradient-to-r from-slate-50 to-[color:var(--dashboard-gold-soft)] px-6 py-4">
                    <div class="text-sm font-semibold text-slate-900">Add visitor coverage</div>
                    <div class="text-xs text-slate-500">Membership {{ $membership->membership_number }}</div>
                </div>
                <div class="p-6 text-slate-900">
                    <form method="POST" action="{{ route('customer.membership.visitors.store') }}" class="grid grid-cols-1 gap-3 rounded-xl border border-slate-200 p-4">
                        @csrf
                        <input name="first_name" required placeholder="Visitor first name" class="rounded-lg border-slate-300 text-sm focus:border-hero-primary focus:ring-hero-primary">
                        <input name="last_name" required placeholder="Visitor last name" class="rounded-lg border-slate-300 text-sm focus:border-hero-primary focus:ring-hero-primary">
                        <input name="phone" placeholder="Phone (optional)" class="rounded-lg border-slate-300 text-sm focus:border-hero-primary focus:ring-hero-primary">
                        <button class="rounded-lg bg-hero-primary px-3 py-2 text-sm font-semibold text-white transition hover:bg-hero-primary-hover">Add visitor</button>
                    </form>
                </div>
            </div>

            <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-md shadow-slate-200/50 ring-1 ring-slate-100">
                <div class="hero-panel-header border-b border-slate-100 bg-gradient-to-r from-slate-50 to-[color:var(--dashboard-gold-soft)] px-6 py-4">
                    <div class="text-sm font-semibold text-slate-900">Current visitors</div>
                    <div class="text-xs text-slate-500">Short-term coverage list</div>
                </div>
                <div class="p-6 text-slate-900">
                    <div class="space-y-3">
                        @forelse($visitors as $dep)
                            <div class="rounded-xl border border-slate-200 p-3">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <div class="font-medium">{{ $dep->first_name }} {{ $dep->last_name }}</div>
                                        <div class="text-xs text-slate-600">Visitor</div>
                                    </div>
                                    <form method="POST" action="{{ route('customer.membership.visitors.destroy', ['dependentId' => $dep->id]) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="rounded-md border border-red-200 px-2 py-1 text-xs font-semibold text-red-700 hover:bg-red-50">Remove</button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <div class="text-sm text-slate-600">No short-term visitors added.</div>
                        @endforelse
                    </div>

                    <div class="mt-5">
                        <a href="{{ route('customer.membership') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-700 shadow-sm transition hover:border-hero-primary hover:text-hero-primary">
                            <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                            Back to membership
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-portal-layout>
