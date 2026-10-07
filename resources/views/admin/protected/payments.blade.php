@extends('layouts.admin')

@section('page_title', 'Protected: Payments')

@section('content')
<div class="space-y-6">
    @include('admin.protected._banner')

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-gray-100">
            <h3 class="font-bold text-gray-900 text-sm">💵 Payment Records <span class="text-[10px] font-mono text-gray-400 ml-2">VIEW_PAYMENTS</span></h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs uppercase tracking-wider text-gray-500">
                    <tr>
                        <th class="text-left px-5 py-3">Payment #</th>
                        <th class="text-left px-5 py-3">Tenant</th>
                        <th class="text-right px-5 py-3">Amount</th>
                        <th class="text-left px-5 py-3">Date</th>
                        <th class="text-left px-5 py-3">Method</th>
                        <th class="text-left px-5 py-3">Reference</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($payments as $p)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-3 font-mono text-xs text-gray-900">{{ $p->payment_number }}</td>
                            <td class="px-5 py-3 font-semibold text-gray-900">{{ $p->tenant?->full_name }}</td>
                            <td class="px-5 py-3 text-right font-semibold">₱{{ number_format($p->amount, 2) }}</td>
                            <td class="px-5 py-3 text-xs text-gray-600">{{ \Carbon\Carbon::parse($p->payment_date)->format('M d, Y') }}</td>
                            <td class="px-5 py-3 text-xs text-gray-600">{{ str_replace('_', ' ', strtoupper($p->payment_method)) }}</td>
                            <td class="px-5 py-3 text-xs font-mono text-gray-500">{{ $p->reference_number ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-8 text-center text-gray-400 text-xs">No payment records.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">{{ $payments->links() }}</div>
    </div>
</div>
@endsection
