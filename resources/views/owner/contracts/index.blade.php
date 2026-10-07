@extends('layouts.owner')

@section('page_title', 'Rental Contracts')

@section('content')
<div class="space-y-6">

    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Rental Contracts & Leases</h2>
            <p class="text-sm text-gray-500">Manage binding lease terms, monthly rent schedules, and deposits.</p>
        </div>
        <a href="{{ route('owner.contracts.create') }}"
           class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-semibold shadow-sm transition">
            + New Rental Contract
        </a>
    </div>

    {{-- Filter --}}
    <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm flex items-center gap-3">
        <a href="{{ route('owner.contracts.index') }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold {{ !request('status') ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-700' }}">All</a>
        <a href="{{ route('owner.contracts.index', ['status' => 'active']) }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold {{ request('status') === 'active' ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-700' }}">Active</a>
        <a href="{{ route('owner.contracts.index', ['status' => 'pending']) }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold {{ request('status') === 'pending' ? 'bg-amber-500 text-white' : 'bg-gray-100 text-gray-700' }}">Pending</a>
        <a href="{{ route('owner.contracts.index', ['status' => 'terminated']) }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold {{ request('status') === 'terminated' ? 'bg-red-600 text-white' : 'bg-gray-100 text-gray-700' }}">Terminated</a>
    </div>

    {{-- Contracts Table --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-600">
                <thead class="bg-gray-50 text-xs uppercase font-semibold text-gray-500 border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-3">Contract #</th>
                        <th class="px-6 py-3">Tenant</th>
                        <th class="px-6 py-3">Unit</th>
                        <th class="px-6 py-3">Monthly Rent</th>
                        <th class="px-6 py-3">Due Day</th>
                        <th class="px-6 py-3">Duration</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($contracts as $contract)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-3.5 font-mono font-bold text-xs text-gray-900">
                                {{ $contract->contract_number }}
                            </td>
                            <td class="px-6 py-3.5 font-medium text-gray-900 text-xs">
                                {{ $contract->tenant?->full_name }}
                            </td>
                            <td class="px-6 py-3.5 text-xs text-gray-700">
                                <span class="font-bold block">{{ $contract->property?->name }}</span>
                                <span class="text-gray-400 font-mono">Rm {{ $contract->room?->room_number }} – Bed {{ $contract->bed?->bed_number }}</span>
                            </td>
                            <td class="px-6 py-3.5 font-bold text-emerald-700 text-xs">
                                ₱{{ number_format($contract->monthly_rent, 2) }}
                            </td>
                            <td class="px-6 py-3.5 text-xs text-gray-600">
                                Day {{ $contract->due_day }}
                            </td>
                            <td class="px-6 py-3.5 text-xs text-gray-500">
                                {{ $contract->start_date->format('M d, Y') }}
                                @if($contract->end_date)
                                    – {{ $contract->end_date->format('M d, Y') }}
                                @else
                                    (Ongoing)
                                @endif
                            </td>
                            <td class="px-6 py-3.5">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold uppercase
                                    {{ $contract->status === 'active' ? 'bg-emerald-100 text-emerald-800' : ($contract->status === 'pending' ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-700') }}">
                                    {{ $contract->status }}
                                </span>
                            </td>
                            <td class="px-6 py-3.5 text-right space-x-2">
                                <a href="{{ route('owner.contracts.show', $contract) }}" class="text-xs text-emerald-600 font-semibold hover:underline">View</a>
                                <a href="{{ route('owner.contracts.edit', $contract) }}" class="text-xs text-gray-500 font-semibold hover:underline">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-8 text-center text-gray-400 text-sm">
                                No rental contracts found. Create your first contract above.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($contracts->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">{{ $contracts->links() }}</div>
        @endif
    </div>

</div>
@endsection

