@extends('layouts.admin')

@section('page_title', 'Protected: Rent Billing')

@section('content')
<div class="space-y-6">
    @include('admin.protected._banner')

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-gray-100">
            <h3 class="font-bold text-gray-900 text-sm">💰 Rent Billing Statements <span class="text-[10px] font-mono text-gray-400 ml-2">VIEW_BILLING</span></h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs uppercase tracking-wider text-gray-500">
                    <tr>
                        <th class="text-left px-5 py-3">Billing #</th>
                        <th class="text-left px-5 py-3">Tenant</th>
                        <th class="text-left px-5 py-3">Period</th>
                        <th class="text-right px-5 py-3">Total</th>
                        <th class="text-right px-5 py-3">Paid</th>
                        <th class="text-right px-5 py-3">Balance</th>
                        <th class="text-left px-5 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($billings as $b)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-3 font-mono text-xs text-gray-900">{{ $b->billing_number }}</td>
                            <td class="px-5 py-3 font-semibold text-gray-900">{{ $b->tenant?->full_name }}</td>
                            <td class="px-5 py-3 text-xs text-gray-600">{{ \Carbon\Carbon::parse($b->billing_period_start)->format('M d, Y') }} → {{ \Carbon\Carbon::parse($b->billing_period_end)->format('M d, Y') }}</td>
                            <td class="px-5 py-3 text-right">₱{{ number_format($b->total_amount, 2) }}</td>
                            <td class="px-5 py-3 text-right">₱{{ number_format($b->amount_paid, 2) }}</td>
                            <td class="px-5 py-3 text-right font-semibold {{ $b->balance > 0 ? 'text-red-600' : 'text-emerald-600' }}">₱{{ number_format($b->balance, 2) }}</td>
                            <td class="px-5 py-3"><span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-gray-100 text-gray-700">{{ $b->status }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-5 py-8 text-center text-gray-400 text-xs">No billing records.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">{{ $billings->links() }}</div>
    </div>
</div>
@endsection
