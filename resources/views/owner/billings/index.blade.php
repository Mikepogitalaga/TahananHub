@extends('layouts.owner')

@section('page_title', 'Rent Billing')

@section('content')
<div class="space-y-6">

    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Rent Invoices & Billing Statements</h2>
            <p class="text-sm text-gray-500">Track monthly billing cycles, outstanding balances, and payment statuses.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('owner.billings.create') }}"
               class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-semibold shadow-sm transition">
                + Generate Billings
            </a>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
        <form method="GET" action="{{ route('owner.billings.index') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search invoice # or tenant name..."
                   class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500">

            <select name="status" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500">
                <option value="">All Statuses</option>
                <option value="unpaid" {{ request('status') === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                <option value="partial" {{ request('status') === 'partial' ? 'selected' : '' }}>Partial</option>
                <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid</option>
                <option value="overdue" {{ request('status') === 'overdue' ? 'selected' : '' }}>Overdue</option>
            </select>

            <div class="flex gap-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-gray-800 text-white rounded-lg text-sm font-medium">Filter</button>
                <a href="{{ route('owner.billings.index') }}" class="px-3 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium">Reset</a>
            </div>
        </form>
    </div>

    {{-- Billings Table --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-600">
                <thead class="bg-gray-50 text-xs uppercase font-semibold text-gray-500 border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-3">Invoice #</th>
                        <th class="px-6 py-3">Tenant & Property</th>
                        <th class="px-6 py-3">Period</th>
                        <th class="px-6 py-3">Due Date</th>
                        <th class="px-6 py-3">Total Due</th>
                        <th class="px-6 py-3">Paid</th>
                        <th class="px-6 py-3">Balance</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($billings as $billing)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-3.5 font-mono font-bold text-xs text-gray-900">
                                {{ $billing->billing_number }}
                            </td>
                            <td class="px-6 py-3.5">
                                <p class="font-bold text-gray-900 text-xs">{{ $billing->tenant?->full_name }}</p>
                                <p class="text-[11px] text-gray-400">{{ $billing->contract?->property?->name }}</p>
                            </td>
                            <td class="px-6 py-3.5 text-xs text-gray-600">
                                {{ $billing->billing_period_start->format('M Y') }}
                            </td>
                            <td class="px-6 py-3.5 text-xs text-gray-600">
                                {{ $billing->due_date->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-3.5 font-bold text-gray-900 text-xs">
                                ₱{{ number_format($billing->total_amount, 2) }}
                            </td>
                            <td class="px-6 py-3.5 text-emerald-600 font-semibold text-xs">
                                ₱{{ number_format($billing->amount_paid, 2) }}
                            </td>
                            <td class="px-6 py-3.5 font-bold text-xs {{ $billing->balance > 0 ? 'text-amber-600' : 'text-gray-400' }}">
                                ₱{{ number_format($billing->balance, 2) }}
                            </td>
                            <td class="px-6 py-3.5">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold uppercase
                                    {{ $billing->status === 'paid' ? 'bg-emerald-100 text-emerald-800' : ($billing->status === 'partial' ? 'bg-blue-100 text-blue-800' : ($billing->status === 'overdue' ? 'bg-red-100 text-red-800' : 'bg-amber-100 text-amber-800')) }}">
                                    {{ $billing->status }}
                                </span>
                            </td>
                            <td class="px-6 py-3.5 text-right space-x-2">
                                <a href="{{ route('owner.billings.show', $billing) }}" class="text-xs text-emerald-600 font-semibold hover:underline">Invoice</a>
                                @if($billing->balance > 0)
                                    <a href="{{ route('owner.payments.create', ['billing_id' => $billing->id]) }}" class="text-xs text-blue-600 font-semibold hover:underline">Record Pay</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-8 text-center text-gray-400 text-sm">
                                No billing records found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($billings->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">{{ $billings->links() }}</div>
        @endif
    </div>

</div>
@endsection

