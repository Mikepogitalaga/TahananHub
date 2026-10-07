@extends('layouts.owner')

@section('title', 'Invoice ' . $billing->billing_number)

@section('content')
<div class="space-y-6 max-w-4xl">
    <!-- Header / Nav -->
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-3">
            <a href="{{ route('owner.billings.index') }}" class="p-2 bg-slate-100 hover:bg-slate-200 rounded-lg text-slate-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <div>
                <div class="flex items-center space-x-3">
                    <h1 class="text-2xl font-bold text-slate-800">{{ $billing->billing_number }}</h1>
                    @if($billing->status === 'paid')
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">PAID</span>
                    @elseif($billing->status === 'partial')
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800">PARTIALLY PAID</span>
                    @elseif($billing->status === 'overdue')
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-800">OVERDUE</span>
                    @else
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-700">UNPAID</span>
                    @endif
                </div>
                <p class="text-xs text-slate-500">Issued on {{ $billing->created_at->format('M d, Y') }} &bull; Period: {{ \Carbon\Carbon::parse($billing->billing_period_start)->format('M d') }} - {{ \Carbon\Carbon::parse($billing->billing_period_end)->format('M d, Y') }}</p>
            </div>
        </div>

        <div class="flex items-center space-x-2">
            <button onclick="window.print()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-lg transition flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Print</span>
            </button>
            @if($billing->balance > 0)
                <a href="{{ route('owner.payments.create', ['billing_id' => $billing->id]) }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg shadow transition flex items-center space-x-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    <span>Record Payment</span>
                </a>
            @endif
        </div>
    </div>

    <!-- Printable Invoice Card -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-8 print:border-none print:shadow-none">
        <!-- Top branding -->
        <div class="flex justify-between items-start border-b border-slate-200 pb-6">
            <div>
                <h2 class="text-2xl font-black text-indigo-700 uppercase tracking-wider">{{ auth()->user()->houseOwner->business_name }}</h2>
                <p class="text-xs text-slate-500 mt-1">{{ auth()->user()->houseOwner->business_address ?? 'Boarding House Operations' }}</p>
                <p class="text-xs text-slate-500">Contact: {{ auth()->user()->phone ?? auth()->user()->email }}</p>
            </div>
            <div class="text-right">
                <div class="text-xs uppercase font-bold text-slate-400">Statement of Account</div>
                <div class="text-xl font-mono font-bold text-slate-800">{{ $billing->billing_number }}</div>
                <div class="text-xs text-slate-500 mt-1">Due Date: <span class="font-bold text-rose-600">{{ \Carbon\Carbon::parse($billing->due_date)->format('F d, Y') }}</span></div>
            </div>
        </div>

        <!-- Tenant & Contract Details -->
        <div class="grid grid-cols-2 gap-6 my-6 text-sm">
            <div>
                <h3 class="text-xs uppercase font-bold text-slate-400 tracking-wider mb-2">Billed To</h3>
                <div class="font-bold text-slate-900 text-base">{{ $billing->tenant->full_name }}</div>
                <div class="text-xs text-slate-600 mt-1">Phone: {{ $billing->tenant->contact_number ?? 'N/A' }}</div>
                <div class="text-xs text-slate-600">Email: {{ $billing->tenant->email ?? 'N/A' }}</div>
            </div>
            <div>
                <h3 class="text-xs uppercase font-bold text-slate-400 tracking-wider mb-2">Location & Lease</h3>
                <div class="font-semibold text-slate-800">{{ $billing->contract->property->name ?? 'N/A' }}</div>
                <div class="text-xs text-slate-600 mt-1">Room {{ $billing->contract->room->room_number ?? '-' }} &bull; Bed {{ $billing->contract->bed->bed_number ?? '-' }}</div>
                <div class="text-xs text-slate-500">Contract # {{ $billing->contract->contract_number }}</div>
            </div>
        </div>

        <!-- Line Items Table -->
        <div class="border border-slate-200 rounded-lg overflow-hidden my-6">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-600 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3">Description</th>
                        <th class="px-4 py-3 text-right">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    <tr>
                        <td class="px-4 py-3 font-medium text-slate-800">
                            Base Room Rent ({{ \Carbon\Carbon::parse($billing->billing_period_start)->format('M d') }} - {{ \Carbon\Carbon::parse($billing->billing_period_end)->format('M d, Y') }})
                        </td>
                        <td class="px-4 py-3 text-right font-semibold text-slate-900">₱{{ number_format($billing->rent_amount, 2) }}</td>
                    </tr>
                    @if($billing->previous_balance > 0)
                        <tr>
                            <td class="px-4 py-3 text-slate-600">Carried Forward Past Due Balance</td>
                            <td class="px-4 py-3 text-right font-semibold text-slate-900">₱{{ number_format($billing->previous_balance, 2) }}</td>
                        </tr>
                    @endif
                    @if($billing->late_fee > 0)
                        <tr>
                            <td class="px-4 py-3 text-slate-600">Late Payment Fee</td>
                            <td class="px-4 py-3 text-right font-semibold text-rose-600">+₱{{ number_format($billing->late_fee, 2) }}</td>
                        </tr>
                    @endif
                    @if($billing->other_charges > 0)
                        <tr>
                            <td class="px-4 py-3 text-slate-600">
                                Additional Charges {{ $billing->other_charges_description ? '(' . $billing->other_charges_description . ')' : '' }}
                            </td>
                            <td class="px-4 py-3 text-right font-semibold text-slate-900">+₱{{ number_format($billing->other_charges, 2) }}</td>
                        </tr>
                    @endif
                    @if($billing->discount > 0)
                        <tr>
                            <td class="px-4 py-3 text-slate-600">Discount applied</td>
                            <td class="px-4 py-3 text-right font-semibold text-emerald-600">-₱{{ number_format($billing->discount, 2) }}</td>
                        </tr>
                    @endif
                </tbody>
                <tfoot class="bg-slate-50 border-t border-slate-200">
                    <tr>
                        <th class="px-4 py-3 text-base font-bold text-slate-800 text-right">Total Statement Due:</th>
                        <th class="px-4 py-3 text-base font-extrabold text-indigo-700 text-right">₱{{ number_format($billing->total_amount, 2) }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Payment Summary Box -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4 rounded-xl bg-slate-50 border border-slate-200">
            <div>
                <span class="block text-xs uppercase font-bold text-slate-400">Total Billed</span>
                <span class="text-xl font-bold text-slate-800">₱{{ number_format($billing->total_amount, 2) }}</span>
            </div>
            <div>
                <span class="block text-xs uppercase font-bold text-slate-400">Total Paid</span>
                <span class="text-xl font-bold text-emerald-600">₱{{ number_format($billing->amount_paid, 2) }}</span>
            </div>
            <div>
                <span class="block text-xs uppercase font-bold text-slate-400">Remaining Balance</span>
                <span class="text-2xl font-extrabold {{ $billing->balance > 0 ? 'text-rose-600' : 'text-slate-700' }}">
                    ₱{{ number_format($billing->balance, 2) }}
                </span>
            </div>
        </div>

        @if($billing->notes)
            <div class="mt-6 p-3 rounded-lg bg-amber-50 border border-amber-200 text-xs text-amber-900">
                <span class="font-bold">Note from Landlord:</span> {{ $billing->notes }}
            </div>
        @endif
    </div>

    <!-- Payments Ledger Card -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6 print:hidden">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-base font-bold text-slate-800">Payments Applied to this Invoice</h3>
                <p class="text-xs text-slate-500">History of receipts credited toward statement {{ $billing->billing_number }}</p>
            </div>
            @if($billing->balance > 0)
                <a href="{{ route('owner.payments.create', ['billing_id' => $billing->id]) }}" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow transition">
                    + Add Payment
                </a>
            @endif
        </div>

        @if($billing->payments->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-slate-500 text-xs uppercase font-semibold">
                        <tr>
                            <th class="px-4 py-2.5">Receipt #</th>
                            <th class="px-4 py-2.5">Date</th>
                            <th class="px-4 py-2.5">Method</th>
                            <th class="px-4 py-2.5">Reference</th>
                            <th class="px-4 py-2.5">Recorded By</th>
                            <th class="px-4 py-2.5 text-right">Amount</th>
                            <th class="px-4 py-2.5 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @foreach($billing->payments as $payment)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3 font-mono font-semibold text-indigo-600">{{ $payment->payment_number }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ \Carbon\Carbon::parse($payment->payment_date)->format('M d, Y') }}</td>
                                <td class="px-4 py-3 uppercase text-xs font-semibold text-slate-700">{{ $payment->payment_method }}</td>
                                <td class="px-4 py-3 text-slate-500 font-mono text-xs">{{ $payment->reference_number ?? '-' }}</td>
                                <td class="px-4 py-3 text-slate-600 text-xs">{{ $payment->recordedBy?->name ?? 'System' }}</td>
                                <td class="px-4 py-3 text-right font-bold text-emerald-600">₱{{ number_format($payment->amount, 2) }}</td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('owner.payments.show', $payment) }}" class="text-xs text-indigo-600 hover:underline font-semibold">View Receipt</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-6 text-sm text-slate-400">
                No payments have been recorded for this invoice yet.
            </div>
        @endif
    </div>

    <!-- Danger Actions -->
    @if($billing->payments->count() === 0)
        <div class="flex justify-end print:hidden">
            <form method="POST" action="{{ route('owner.billings.destroy', $billing) }}" onsubmit="return confirm('Are you sure you want to cancel and delete this invoice?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-xs text-rose-600 hover:text-rose-800 font-semibold hover:underline">
                    Delete this Invoice
                </button>
            </form>
        </div>
    @endif
</div>
@endsection

