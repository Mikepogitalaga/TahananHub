@extends('layouts.tenant')

@section('title', 'Rent & Billings')

@section('content')
<div class="space-y-6" x-data="{ tab: 'invoices' }">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Rent Invoices & Payment Receipts</h1>
            <p class="text-sm text-slate-500">Track your monthly billing statements, due dates, and verified payments.</p>
        </div>

        <div class="bg-white border border-slate-200 px-5 py-3 rounded-xl shadow-sm text-right">
            <span class="text-xs uppercase font-bold text-slate-400 block">Current Balance Due</span>
            <span class="text-2xl font-black {{ $totalBalance > 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                ₱{{ number_format($totalBalance, 2) }}
            </span>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex space-x-2 border-b border-slate-200">
        <button @click="tab = 'invoices'" :class="tab === 'invoices' ? 'border-b-2 border-indigo-600 text-indigo-700 font-bold' : 'text-slate-500 hover:text-slate-700 font-medium'" class="pb-3 px-4 text-sm transition">
            Billing Statements ({{ $billings->total() }})
        </button>
        <button @click="tab = 'payments'" :class="tab === 'payments' ? 'border-b-2 border-indigo-600 text-indigo-700 font-bold' : 'text-slate-500 hover:text-slate-700 font-medium'" class="pb-3 px-4 text-sm transition">
            Payment Receipts ({{ $payments->total() }})
        </button>
    </div>

    <!-- Invoices Tab Panel -->
    <div x-show="tab === 'invoices'" class="space-y-4">
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 uppercase text-xs">
                        <tr>
                            <th class="px-5 py-3">Invoice #</th>
                            <th class="px-5 py-3">Billing Period</th>
                            <th class="px-5 py-3">Due Date</th>
                            <th class="px-5 py-3 text-right">Total Billed</th>
                            <th class="px-5 py-3 text-right">Paid</th>
                            <th class="px-5 py-3 text-right">Balance</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse($billings as $b)
                            <tr class="hover:bg-slate-50">
                                <td class="px-5 py-3.5 font-mono font-bold text-indigo-600">
                                    <a href="{{ route('tenant.billings.show-billing', $b) }}" class="hover:underline">{{ $b->billing_number }}</a>
                                </td>
                                <td class="px-5 py-3.5 text-xs text-slate-600">
                                    {{ \Carbon\Carbon::parse($b->billing_period_start)->format('M d') }} - {{ \Carbon\Carbon::parse($b->billing_period_end)->format('M d, Y') }}
                                </td>
                                <td class="px-5 py-3.5 text-slate-700 text-xs font-semibold">
                                    {{ \Carbon\Carbon::parse($b->due_date)->format('M d, Y') }}
                                </td>
                                <td class="px-5 py-3.5 text-right font-medium text-slate-800">₱{{ number_format($b->total_amount, 2) }}</td>
                                <td class="px-5 py-3.5 text-right font-medium text-emerald-600">₱{{ number_format($b->amount_paid, 2) }}</td>
                                <td class="px-5 py-3.5 text-right font-extrabold {{ $b->balance > 0 ? 'text-rose-600' : 'text-slate-700' }}">
                                    ₱{{ number_format($b->balance, 2) }}
                                </td>
                                <td class="px-5 py-3.5">
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase
                                        @if($b->status === 'paid') bg-emerald-100 text-emerald-800
                                        @elseif($b->status === 'partial') bg-amber-100 text-amber-800
                                        @elseif($b->status === 'overdue') bg-rose-100 text-rose-800
                                        @else bg-slate-100 text-slate-700
                                        @endif">
                                        {{ $b->status }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-right">
                                    <a href="{{ route('tenant.billings.show-billing', $b) }}" class="px-3 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded text-xs font-semibold transition">
                                        View Statement
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-10 text-slate-400">
                                    No billing statements found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($billings->hasPages())
                <div class="p-4 border-t border-slate-200">
                    {{ $billings->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Payments Tab Panel -->
    <div x-show="tab === 'payments'" class="space-y-4" style="display: none;">
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 uppercase text-xs">
                        <tr>
                            <th class="px-5 py-3">Receipt #</th>
                            <th class="px-5 py-3">Date</th>
                            <th class="px-5 py-3">Invoice Paid</th>
                            <th class="px-5 py-3">Payment Method</th>
                            <th class="px-5 py-3">Reference #</th>
                            <th class="px-5 py-3 text-right">Amount Paid</th>
                            <th class="px-5 py-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse($payments as $p)
                            <tr class="hover:bg-slate-50">
                                <td class="px-5 py-3.5 font-mono font-bold text-indigo-600">
                                    <a href="{{ route('tenant.billings.show-payment', $p) }}" class="hover:underline">{{ $p->payment_number }}</a>
                                </td>
                                <td class="px-5 py-3.5 text-slate-600 text-xs">{{ \Carbon\Carbon::parse($p->payment_date)->format('M d, Y') }}</td>
                                <td class="px-5 py-3.5 font-mono text-xs text-slate-600">
                                    {{ $p->billing->billing_number }}
                                </td>
                                <td class="px-5 py-3.5 uppercase text-xs font-semibold text-slate-700">{{ $p->payment_method }}</td>
                                <td class="px-5 py-3.5 font-mono text-xs text-slate-500">{{ $p->reference_number ?? '-' }}</td>
                                <td class="px-5 py-3.5 text-right font-extrabold text-emerald-600 text-base">₱{{ number_format($p->amount, 2) }}</td>
                                <td class="px-5 py-3.5 text-right">
                                    <a href="{{ route('tenant.billings.show-payment', $p) }}" class="px-3 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded text-xs font-semibold transition">
                                        View Receipt
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-10 text-slate-400">
                                    No payment receipts recorded yet.
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
</div>
@endsection

