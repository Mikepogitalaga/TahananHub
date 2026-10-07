@extends('layouts.owner')

@section('title', 'Receipt ' . $payment->payment_number)

@section('content')
<div class="space-y-6 max-w-2xl mx-auto">
    <div class="flex items-center justify-between print:hidden">
        <a href="{{ route('owner.payments.index') }}" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-lg transition flex items-center space-x-1">
            <span>&larr; Back to Payments</span>
        </a>

        <div class="flex items-center space-x-2">
            <button onclick="window.print()" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white text-sm font-semibold rounded-lg shadow transition flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Print Official Receipt</span>
            </button>
        </div>
    </div>

    <!-- Official Receipt Paper Card -->
    <div class="bg-white border-2 border-dashed border-slate-300 rounded-2xl shadow-sm p-8 print:border-none print:shadow-none print:p-0">
        <!-- Header -->
        <div class="text-center border-b border-slate-200 pb-6">
            <div class="inline-block px-3 py-1 bg-emerald-100 text-emerald-800 font-extrabold text-xs uppercase tracking-widest rounded-full mb-2">
                Payment Acknowledgment Receipt
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">{{ auth()->user()->houseOwner->business_name }}</h1>
            <p class="text-xs text-slate-500 mt-1">{{ auth()->user()->houseOwner->business_address ?? 'Boarding House Operations' }}</p>
            <p class="text-xs text-slate-500">Contact: {{ auth()->user()->phone ?? auth()->user()->email }}</p>
        </div>

        <!-- Receipt Meta -->
        <div class="flex justify-between items-center py-4 border-b border-slate-100 text-sm">
            <div>
                <span class="text-xs text-slate-400 uppercase font-bold block">Official Receipt #</span>
                <span class="font-mono font-bold text-slate-800 text-base">{{ $payment->payment_number }}</span>
            </div>
            <div class="text-right">
                <span class="text-xs text-slate-400 uppercase font-bold block">Date of Payment</span>
                <span class="font-semibold text-slate-800">{{ \Carbon\Carbon::parse($payment->payment_date)->format('F d, Y') }}</span>
            </div>
        </div>

        <!-- Details -->
        <div class="space-y-4 py-6 text-sm">
            <div class="flex justify-between items-start">
                <span class="text-slate-500 font-medium">Received From:</span>
                <span class="font-bold text-slate-900 text-right">{{ $payment->tenant->full_name }}</span>
            </div>

            <div class="flex justify-between items-start">
                <span class="text-slate-500 font-medium">Property / Unit:</span>
                <span class="font-semibold text-slate-800 text-right">
                    {{ $payment->billing->contract->property->name ?? '-' }} (Room {{ $payment->billing->contract->room->room_number ?? '-' }})
                </span>
            </div>

            <div class="flex justify-between items-start">
                <span class="text-slate-500 font-medium">Applied to Invoice:</span>
                <span class="font-mono font-semibold text-indigo-600 text-right">
                    <a href="{{ route('owner.billings.show', $payment->billing_id) }}" class="hover:underline">
                        {{ $payment->billing->billing_number }}
                    </a>
                </span>
            </div>

            <div class="flex justify-between items-start">
                <span class="text-slate-500 font-medium">Payment Mode:</span>
                <span class="uppercase font-bold text-slate-800">{{ $payment->payment_method }}</span>
            </div>

            @if($payment->reference_number)
                <div class="flex justify-between items-start">
                    <span class="text-slate-500 font-medium">Transaction / Ref #:</span>
                    <span class="font-mono text-slate-800 text-right">{{ $payment->reference_number }}</span>
                </div>
            @endif

            @if($payment->notes)
                <div class="flex justify-between items-start">
                    <span class="text-slate-500 font-medium">Remarks:</span>
                    <span class="text-slate-700 text-right italic">{{ $payment->notes }}</span>
                </div>
            @endif
        </div>

        <!-- Amount Box -->
        <div class="my-4 p-5 rounded-xl bg-emerald-50 border border-emerald-200 text-center">
            <div class="text-xs uppercase font-extrabold text-emerald-800 tracking-wider">Amount Received</div>
            <div class="text-3xl font-black text-emerald-600 mt-1">
                ₱{{ number_format($payment->amount, 2) }}
            </div>
        </div>

        <!-- Remaining Balance Alert -->
        <div class="text-center text-xs text-slate-500 border-b border-slate-100 pb-6">
            Invoice Balance After Payment:
            <span class="font-bold {{ $payment->billing->balance > 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                ₱{{ number_format($payment->billing->balance, 2) }}
            </span>
            ({{ strtoupper($payment->billing->status) }})
        </div>

        <!-- Signatures / Footer -->
        <div class="pt-8 grid grid-cols-2 gap-8 text-center text-xs">
            <div>
                <div class="border-b border-slate-300 pb-8"></div>
                <div class="mt-2 font-semibold text-slate-700">{{ $payment->tenant->full_name }}</div>
                <div class="text-slate-400">Tenant Signature</div>
            </div>
            <div>
                <div class="border-b border-slate-300 pb-8"></div>
                <div class="mt-2 font-semibold text-slate-700">{{ $payment->recordedBy?->name ?? auth()->user()->name }}</div>
                <div class="text-slate-400">Authorized Representative / Landlord</div>
            </div>
        </div>
    </div>
</div>
@endsection

