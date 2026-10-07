@extends('layouts.tenant')

@section('title', 'Statement ' . $billing->billing_number)

@section('content')
<div class="space-y-6 max-w-3xl mx-auto">
    <div class="flex items-center justify-between print:hidden">
        <a href="{{ route('tenant.billings') }}" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-lg transition flex items-center space-x-1">
            <span>&larr; Back to Billings</span>
        </a>

        <button onclick="window.print()" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white text-sm font-semibold rounded-lg shadow transition flex items-center space-x-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            <span>Print Statement</span>
        </button>
    </div>

    <!-- Printable Statement Card -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-8 print:border-none print:shadow-none print:p-0 space-y-6">
        <div class="flex justify-between items-start border-b border-slate-200 pb-6">
            <div>
                <span class="text-xs uppercase font-extrabold tracking-wider text-indigo-700">Billing Statement</span>
                <h1 class="text-xl font-black text-slate-900 mt-1">{{ $billing->contract->property->houseOwner->business_name }}</h1>
                <p class="text-xs text-slate-500">{{ $billing->contract->property->name }} &bull; Room {{ $billing->contract->room?->room_number }}</p>
            </div>
            <div class="text-right">
                <span class="font-mono font-bold text-slate-800 text-lg block">{{ $billing->billing_number }}</span>
                <span class="text-xs text-slate-500 block">Due Date: <strong class="text-rose-600">{{ \Carbon\Carbon::parse($billing->due_date)->format('M d, Y') }}</strong></span>
                <div class="mt-1">
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase
                        @if($billing->status === 'paid') bg-emerald-100 text-emerald-800
                        @elseif($billing->status === 'partial') bg-amber-100 text-amber-800
                        @elseif($billing->status === 'overdue') bg-rose-100 text-rose-800
                        @else bg-slate-100 text-slate-700
                        @endif">
                        {{ $billing->status }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Breakdown Table -->
        <table class="w-full text-left text-sm border border-slate-200 rounded-lg overflow-hidden">
            <thead class="bg-slate-50 text-slate-600 uppercase text-xs">
                <tr>
                    <th class="px-4 py-3">Item Description</th>
                    <th class="px-4 py-3 text-right">Amount</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                <tr>
                    <td class="px-4 py-3 font-medium text-slate-800">
                        Room Rent ({{ \Carbon\Carbon::parse($billing->billing_period_start)->format('M d') }} - {{ \Carbon\Carbon::parse($billing->billing_period_end)->format('M d, Y') }})
                    </td>
                    <td class="px-4 py-3 text-right font-semibold text-slate-900">₱{{ number_format($billing->rent_amount, 2) }}</td>
                </tr>
                @if($billing->previous_balance > 0)
                    <tr>
                        <td class="px-4 py-3 text-slate-600">Past Due Balance Carried Over</td>
                        <td class="px-4 py-3 text-right font-semibold text-slate-900">₱{{ number_format($billing->previous_balance, 2) }}</td>
                    </tr>
                @endif
                @if($billing->late_fee > 0)
                    <tr>
                        <td class="px-4 py-3 text-slate-600">Late Payment Penalty</td>
                        <td class="px-4 py-3 text-right font-semibold text-rose-600">+₱{{ number_format($billing->late_fee, 2) }}</td>
                    </tr>
                @endif
                @if($billing->other_charges > 0)
                    <tr>
                        <td class="px-4 py-3 text-slate-600">Additional Charges ({{ $billing->other_charges_description ?: 'Utilities' }})</td>
                        <td class="px-4 py-3 text-right font-semibold text-slate-900">+₱{{ number_format($billing->other_charges, 2) }}</td>
                    </tr>
                @endif
                @if($billing->discount > 0)
                    <tr>
                        <td class="px-4 py-3 text-slate-600">Discount</td>
                        <td class="px-4 py-3 text-right font-semibold text-emerald-600">-₱{{ number_format($billing->discount, 2) }}</td>
                    </tr>
                @endif
            </tbody>
            <tfoot class="bg-slate-50 border-t border-slate-200">
                <tr>
                    <th class="px-4 py-3 text-sm font-bold text-slate-800 text-right">Total Statement:</th>
                    <th class="px-4 py-3 text-base font-extrabold text-indigo-700 text-right">₱{{ number_format($billing->total_amount, 2) }}</th>
                </tr>
            </tfoot>
        </table>

        <!-- Summary -->
        <div class="grid grid-cols-3 gap-4 p-4 rounded-xl bg-slate-50 border border-slate-200 text-center">
            <div>
                <span class="text-xs uppercase text-slate-400 block font-bold">Total Due</span>
                <span class="text-lg font-bold text-slate-800">₱{{ number_format($billing->total_amount, 2) }}</span>
            </div>
            <div>
                <span class="text-xs uppercase text-slate-400 block font-bold">Total Paid</span>
                <span class="text-lg font-bold text-emerald-600">₱{{ number_format($billing->amount_paid, 2) }}</span>
            </div>
            <div>
                <span class="text-xs uppercase text-slate-400 block font-bold">Remaining Balance</span>
                <span class="text-xl font-extrabold {{ $billing->balance > 0 ? 'text-rose-600' : 'text-slate-800' }}">₱{{ number_format($billing->balance, 2) }}</span>
            </div>
        </div>

        @if($billing->notes)
            <div class="p-3 bg-amber-50 border border-amber-200 rounded-lg text-xs text-amber-900">
                <strong>Landlord Reminder:</strong> {{ $billing->notes }}
            </div>
        @endif
    </div>
</div>
@endsection

