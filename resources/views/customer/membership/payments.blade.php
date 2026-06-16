<x-portal-layout>
    <div class="w-full max-w-none space-y-6">
        <div>
            @include('customer.membership.partials.portal-eyebrow')
            <h1 class="font-display mt-1 text-2xl font-semibold tracking-tight text-slate-900">Payment history & invoices</h1>
            <p class="mt-1 text-sm text-slate-600">View past payments and download invoices.</p>
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-md shadow-slate-200/50 ring-1 ring-slate-100">
            <div class="hero-panel-header border-b border-slate-100 bg-gradient-to-r from-slate-50 to-[color:var(--dashboard-gold-soft)] px-6 py-4">
                <div class="text-sm font-semibold text-slate-900">Invoices</div>
                <div class="text-xs text-slate-500">Membership {{ $membership->membership_number }}</div>
            </div>
            <div class="p-6 text-slate-900">
                <div class="overflow-x-auto rounded-xl border border-slate-200">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-3 py-2 text-left font-semibold text-slate-600">Invoice</th>
                                <th class="px-3 py-2 text-left font-semibold text-slate-600">Period</th>
                                <th class="px-3 py-2 text-left font-semibold text-slate-600">Paid at</th>
                                <th class="px-3 py-2 text-left font-semibold text-slate-600">Amount</th>
                                <th class="px-3 py-2 text-left font-semibold text-slate-600">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse($payments as $payment)
                                <tr>
                                    <td class="px-3 py-2 font-mono text-xs">{{ $payment['invoice'] }}</td>
                                    <td class="px-3 py-2">{{ $payment['period'] }}</td>
                                    <td class="px-3 py-2">{{ $payment['paid_at'] }}</td>
                                    <td class="px-3 py-2">${{ $payment['amount'] }}</td>
                                    <td class="px-3 py-2">
                                        <a href="{{ route('customer.membership.invoices.download', ['invoiceRef' => $payment['invoice']]) }}" class="text-xs font-semibold text-hero-primary hover:underline">
                                            Download invoice
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-3 py-4 text-center text-slate-500">No payment history available.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
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
</x-portal-layout>
