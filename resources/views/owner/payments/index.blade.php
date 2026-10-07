@extends('layouts.owner')

@section('title', 'Payment Receipts')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Rent Payments & Collections</h1>
            <p class="text-sm text-slate-500">Track all tenant collections, issue digital receipts, and verify reference numbers.</p>
        </div>
        <a href="{{ route('owner.payments.create') }}" class="inline-flex items-center space-x-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-sm rounded-lg shadow transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>Record New Payment</span>
        </a>
    </div>

    <!-- Filters & Search -->
    <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
        <form method="GET" action="{{ route('owner.payments.index') }}" class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search receipt #, ref #, tenant name..." class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500">
            </div>
            <div>
                <select name="method" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm bg-white focus:ring-2 focus:ring-emerald-500">
                    <option value="">-- All Payment Methods --</option>
                    <option value="cash" {{ request('method') === 'cash' ? 'selected' : '' }}>Cash</option>
                    <option value="gcash" {{ request('method') === 'gcash' ? 'selected' : '' }}>GCash</option>
                    <option value="bank_transfer" {{ request('method') === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                    <option value="online_payment" {{ request('method') === 'online_payment' ? 'selected' : '' }}>Online Payment</option>
                    <option value="other" {{ request('method') === 'other' ? 'selected' : '' }}>Other</option>
                </select>
            </div>
            <div class="flex items-center space-x-2">
                <button type="submit" class="w-full px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white font-semibold text-sm rounded-lg transition">Filter</button>
                @if(request()->anyFilled(['search', 'method']))
                    <a href="{{ route('owner.payments.index') }}" class="px-3 py-2 text-slate-500 hover:text-slate-800 text-sm font-semibold">Reset</a>
                @endif
            </div>
        </form>
    </div>

    <!-- Payments Table -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 uppercase text-xs">
                    <tr>
                        <th class="px-5 py-3">Receipt #</th>
                        <th class="px-5 py-3">Date</th>
                        <th class="px-5 py-3">Tenant</th>
                        <th class="px-5 py-3">Property</th>
                        <th class="px-5 py-3">Invoice Applied</th>
                        <th class="px-5 py-3">Method</th>
                        <th class="px-5 py-3 text-right">Amount</th>
                        <th class="px-5 py-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($payments as $p)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-3.5 font-mono font-bold text-indigo-600">
                                <a href="{{ route('owner.payments.show', $p) }}" class="hover:underline">{{ $p->payment_number }}</a>
                            </td>
                            <td class="px-5 py-3.5 text-slate-600">
                                {{ \Carbon\Carbon::parse($p->payment_date)->format('M d, Y') }}
                            </td>
                            <td class="px-5 py-3.5 font-semibold text-slate-800">
                                {{ $p->tenant->full_name }}
                            </td>
                            <td class="px-5 py-3.5 text-slate-600 text-xs">
                                {{ $p->billing->contract->property->name ?? '-' }}
                            </td>
                            <td class="px-5 py-3.5 font-mono text-xs text-slate-600">
                                <a href="{{ route('owner.billings.show', $p->billing_id) }}" class="text-indigo-600 hover:underline">
                                    {{ $p->billing->billing_number }}
                                </a>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold uppercase bg-slate-100 text-slate-700">
                                    {{ $p->payment_method }}
                                </span>
                                @if($p->reference_number)
                                    <div class="text-[11px] font-mono text-slate-400 mt-0.5">Ref: {{ $p->reference_number }}</div>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-right font-extrabold text-emerald-600 text-base">
                                ₱{{ number_format($p->amount, 2) }}
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <a href="{{ route('owner.payments.show', $p) }}" class="px-3 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded text-xs font-semibold transition">
                                    View Receipt
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-10 text-slate-400">
                                No payment receipts found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($payments->hasPages())
            <div class="p-4 border-t border-slate-200">
                {{ $payments->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

