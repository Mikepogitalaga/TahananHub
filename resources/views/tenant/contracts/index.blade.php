@extends('layouts.tenant')

@section('title', 'My Lease Contract')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-800">Rental Agreement & Contracts</h1>
        <p class="text-sm text-slate-500">View your active lease terms, monthly rate, security deposit, and house rules.</p>
    </div>

    <div class="space-y-4">
        @forelse($contracts as $c)
            <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm hover:border-slate-300 transition">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-100">
                    <div>
                        <div class="flex items-center space-x-2">
                            <span class="font-mono font-bold text-indigo-600">{{ $c->contract_number }}</span>
                            @if($c->status === 'active')
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase bg-emerald-100 text-emerald-800">Active Lease</span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase bg-slate-100 text-slate-600">{{ $c->status }}</span>
                            @endif
                        </div>
                        <h2 class="text-lg font-bold text-slate-800 mt-1">{{ $c->property->name }}</h2>
                        <p class="text-xs text-slate-500">{{ $c->property->address }}</p>
                    </div>

                    <div class="text-right">
                        <span class="text-xs uppercase font-bold text-slate-400 block">Monthly Rent</span>
                        <span class="text-2xl font-black text-indigo-700">₱{{ number_format($c->monthly_rent, 2) }}</span>
                    </div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 py-4 text-xs">
                    <div>
                        <span class="text-slate-400 block">Assigned Unit</span>
                        <span class="font-bold text-slate-800">Room {{ $c->room?->room_number }} / Bed {{ $c->bed?->bed_number }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block">Lease Start</span>
                        <span class="font-semibold text-slate-800">{{ \Carbon\Carbon::parse($c->start_date)->format('M d, Y') }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block">Monthly Due Day</span>
                        <span class="font-semibold text-slate-800">Day {{ $c->due_day }} of month</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block">Security Deposit</span>
                        <span class="font-semibold text-slate-800">₱{{ number_format($c->security_deposit, 2) }}</span>
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-100 flex justify-end">
                    <a href="{{ route('tenant.contracts.show', $c) }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-lg shadow transition">
                        View Full Agreement & Terms &rarr;
                    </a>
                </div>
            </div>
        @empty
            <div class="bg-white border border-slate-200 rounded-2xl p-12 text-center text-slate-400 shadow-sm">
                <div class="text-3xl mb-2">📄</div>
                <h3 class="text-base font-semibold text-slate-700">No Rental Contracts Found</h3>
                <p class="text-xs mt-1">Your lease contract will appear here once finalized by your landlord.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection

