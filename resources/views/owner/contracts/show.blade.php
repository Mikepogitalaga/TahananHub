@extends('layouts.owner')

@section('page_title', 'Contract ' . $contract->contract_number)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    {{-- Actions Bar --}}
    <div class="flex items-center justify-between">
        <a href="{{ route('owner.contracts.index') }}" class="text-xs text-emerald-600 hover:underline">← Back to Contracts</a>
        <div class="flex items-center gap-2">
            <a href="{{ route('owner.contracts.edit', $contract) }}" class="px-3.5 py-1.5 border border-gray-300 rounded-lg text-xs font-semibold text-gray-700 hover:bg-gray-50">
                Edit Contract
            </a>
            <button onclick="window.print()" class="px-3.5 py-1.5 bg-gray-800 hover:bg-gray-900 text-white rounded-lg text-xs font-semibold">
                🖨️ Print Lease
            </button>
        </div>
    </div>

    {{-- Contract Document Style Card --}}
    <div class="bg-white rounded-2xl border border-gray-200 p-8 shadow-sm space-y-6">
        
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center pb-6 border-b border-gray-100 gap-4">
            <div>
                <span class="text-xs uppercase font-bold tracking-wider text-emerald-600">Rental Lease Agreement</span>
                <h2 class="text-2xl font-bold font-mono text-gray-900 mt-1">{{ $contract->contract_number }}</h2>
                <p class="text-xs text-gray-500">Created: {{ $contract->created_at->format('M d, Y') }}</p>
            </div>
            <div class="text-right">
                <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider
                    {{ $contract->status === 'active' ? 'bg-emerald-100 text-emerald-800' : ($contract->status === 'pending' ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-700') }}">
                    {{ $contract->status }}
                </span>
            </div>
        </div>

        {{-- Parties Grid --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 text-sm">
            <div class="p-4 bg-gray-50 rounded-xl space-y-1">
                <span class="text-[10px] uppercase font-bold text-gray-400 block mb-1">Tenant (Lessee)</span>
                <p class="font-bold text-base text-gray-900">{{ $contract->tenant?->full_name }}</p>
                <p class="text-xs text-gray-600">Phone: {{ $contract->tenant?->contact_number ?? '—' }}</p>
                <p class="text-xs text-gray-600">Email: {{ $contract->tenant?->email ?? '—' }}</p>
            </div>

            <div class="p-4 bg-gray-50 rounded-xl space-y-1">
                <span class="text-[10px] uppercase font-bold text-gray-400 block mb-1">Leased Unit (Premises)</span>
                <p class="font-bold text-base text-gray-900">{{ $contract->property?->name }}</p>
                <p class="text-xs text-gray-600 font-mono">Room {{ $contract->room?->room_number }} – Bed {{ $contract->bed?->bed_number }}</p>
                <p class="text-xs text-gray-500">{{ $contract->property?->address }}, {{ $contract->property?->municipality }}</p>
            </div>
        </div>

        {{-- Financial Terms Breakdown --}}
        <div>
            <h3 class="text-xs uppercase font-bold text-gray-400 tracking-wider mb-3">Financial Terms & Rent Schedule</h3>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm bg-emerald-50/50 p-4 rounded-xl border border-emerald-100">
                <div>
                    <span class="text-xs text-gray-500 block">Monthly Rent</span>
                    <span class="text-lg font-bold text-emerald-800">₱{{ number_format($contract->monthly_rent, 2) }}</span>
                </div>
                <div>
                    <span class="text-xs text-gray-500 block">Due Day</span>
                    <span class="text-base font-bold text-gray-800">Day {{ $contract->due_day }} of month</span>
                </div>
                <div>
                    <span class="text-xs text-gray-500 block">Security Deposit</span>
                    <span class="text-base font-bold text-gray-800">₱{{ number_format($contract->security_deposit, 2) }}</span>
                </div>
                <div>
                    <span class="text-xs text-gray-500 block">Advance Rent</span>
                    <span class="text-base font-bold text-gray-800">₱{{ number_format($contract->advance_payment, 2) }}</span>
                </div>
            </div>
        </div>

        {{-- Duration --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-xs text-gray-600 border-t border-gray-100 pt-4">
            <div>
                <span class="text-gray-400 block">Lease Commencement</span>
                <strong class="text-gray-800 text-sm">{{ $contract->start_date->format('F d, Y') }}</strong>
            </div>
            <div>
                <span class="text-gray-400 block">Lease Expiration</span>
                <strong class="text-gray-800 text-sm">{{ $contract->end_date ? $contract->end_date->format('F d, Y') : 'Periodic (Month-to-Month)' }}</strong>
            </div>
            <div>
                <span class="text-gray-400 block">Late Payment Fee</span>
                <strong class="text-gray-800 text-sm">₱{{ number_format($contract->late_fee, 2) }}</strong>
            </div>
        </div>

        {{-- Terms --}}
        @if($contract->terms)
            <div class="border-t border-gray-100 pt-4">
                <h3 class="text-xs uppercase font-bold text-gray-400 tracking-wider mb-2">Rules & Special Terms</h3>
                <div class="p-3 bg-gray-50 rounded-lg text-xs text-gray-700 whitespace-pre-line leading-relaxed">
                    {{ $contract->terms }}
                </div>
            </div>
        @endif

    </div>

    {{-- Associated Billings Section --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm">
        <h3 class="font-bold text-gray-900 text-sm mb-4">Billing History for this Contract</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-gray-600">
                <thead class="bg-gray-50 text-[11px] uppercase font-semibold text-gray-500">
                    <tr>
                        <th class="py-2.5 px-3">Billing #</th>
                        <th class="py-2.5 px-3">Period</th>
                        <th class="py-2.5 px-3">Total Amount</th>
                        <th class="py-2.5 px-3">Paid</th>
                        <th class="py-2.5 px-3">Balance</th>
                        <th class="py-2.5 px-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($contract->rentBillings as $b)
                        <tr>
                            <td class="py-2.5 px-3 font-mono font-bold text-gray-900">{{ $b->billing_number }}</td>
                            <td class="py-2.5 px-3">{{ $b->billing_period_start->format('M Y') }}</td>
                            <td class="py-2.5 px-3 font-semibold text-gray-800">₱{{ number_format($b->total_amount, 2) }}</td>
                            <td class="py-2.5 px-3 text-emerald-600">₱{{ number_format($b->amount_paid, 2) }}</td>
                            <td class="py-2.5 px-3 font-bold text-amber-600">₱{{ number_format($b->balance, 2) }}</td>
                            <td class="py-2.5 px-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase
                                    {{ $b->status === 'paid' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                    {{ $b->status }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-4 text-center text-gray-400">No billing generated yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

