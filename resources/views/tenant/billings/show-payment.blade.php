@extends('layouts.tenant')

@section('title', 'Receipt ' . $payment->payment_number)

@section('content')
<div class="space-y-6 max-w-2xl mx-auto">
    <div class="flex items-center justify-between print:hidden">
        <a href="{{ route('tenant.billings') }}" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-lg transition flex items-center space-x-1">
            <span>&larr; Back to Billings</span>
        </a>

        <button onclick="window.print()" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white text-sm font-semibold rounded-lg shadow transition flex items-center space-x-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            <span>Print Receipt</span>
        </button>
    </div>

    <!-- Official Receipt Card -->
    <div class="bg-white border-2 border-dashed border-slate-300 rounded-2xl shadow-sm p-8 print:border-none print:shadow-none print:p-0">
        <div class="text-center border-b border-slate-200 pb-6">
            <div class="inline-block px-3 py-1 bg-emerald-100 text-emerald-800 font-extrabold text-xs uppercase tracking-widest rounded-full mb-2">
                Official Payment Receipt
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">{{ $payment->billing->contract->property->houseOwner->business_name }}</h1>
            <p class="text-xs text-slate-500 mt-1">{{ $payment->billing->contract->property->address }}</p>
        </div>

        <div class="flex justify-between items-center py-4 border-b border-slate-100 text-sm">
            <div>
                <span class="text-xs text-slate-400 uppercase font-bold block">Receipt #</span>
                <span class="font-mono font-bold text-slate-800 text-base">{{ $payment->payment_number }}</span>
            </div>
            <div class="text-right">
                <span class="text-xs text-slate-400 uppercase font-bold block">Date of Payment</span>
                <span class="font-semibold text-slate-800">{{ \Carbon\Carbon::parse($payment->payment_date)->format('F d, Y') }}</span>
            </div>
        </div>

        <div class="space-y-3 py-6 text-sm">
            <div class="flex justify-between">
                <span class="text-slate-500">Tenant:</span>
                <span class="font-bold text-slate-900">{{ $tenant->full_name }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">Room:</span>
                <span class="font-semibold text-slate-800">Unit {{ $payment->billing->contract->room?->room_number }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">Payment Method:</span>
                <span class="uppercase font-bold text-slate-800">{{ $payment->payment_method }}</span>
            </div>
            @if($payment->reference_number)
                <div class="flex justify-between">
                    <span class="text-slate-500">Ref / Trans #:</span>
                    <span class="font-mono text-slate-800">{{ $payment->reference_number }}</span>
                </div>
            @endif
        </div>

        <div class="my-4 p-5 rounded-xl bg-emerald-50 border border-emerald-200 text-center">
            <div class="text-xs uppercase font-extrabold text-emerald-800 tracking-wider">Amount Paid</div>
            <div class="text-3xl font-black text-emerald-600 mt-1">
                ₱{{ number_format($payment->amount, 2) }}
            </div>
        </div>

        <div class="text-center text-xs text-slate-400 pt-4">
            Recorded by {{ $payment->recordedBy?->name ?? 'Property Management' }} &bull; Thank you for your payment!
        </div>
    </div>
</div>
@endsection

