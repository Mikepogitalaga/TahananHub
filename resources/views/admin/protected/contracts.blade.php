@extends('layouts.admin')

@section('page_title', 'Protected: Contracts')

@section('content')
<div class="space-y-6">
    @include('admin.protected._banner')

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-gray-100">
            <h3 class="font-bold text-gray-900 text-sm">📝 Rental Contracts <span class="text-[10px] font-mono text-gray-400 ml-2">VIEW_CONTRACTS</span></h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs uppercase tracking-wider text-gray-500">
                    <tr>
                        <th class="text-left px-5 py-3">Contract #</th>
                        <th class="text-left px-5 py-3">Tenant</th>
                        <th class="text-left px-5 py-3">Property / Room</th>
                        <th class="text-left px-5 py-3">Period</th>
                        <th class="text-right px-5 py-3">Monthly Rent</th>
                        <th class="text-left px-5 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($contracts as $c)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-3 font-mono text-xs text-gray-900">{{ $c->contract_number }}</td>
                            <td class="px-5 py-3 font-semibold text-gray-900">{{ $c->tenant?->full_name }}</td>
                            <td class="px-5 py-3 text-xs text-gray-600">{{ $c->property?->name }} · {{ $c->room?->room_number }}</td>
                            <td class="px-5 py-3 text-xs text-gray-600">{{ \Carbon\Carbon::parse($c->start_date)->format('M d, Y') }} → {{ $c->end_date ? \Carbon\Carbon::parse($c->end_date)->format('M d, Y') : 'Ongoing' }}</td>
                            <td class="px-5 py-3 text-right">₱{{ number_format($c->monthly_rent, 2) }}</td>
                            <td class="px-5 py-3"><span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-gray-100 text-gray-700">{{ $c->status }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-8 text-center text-gray-400 text-xs">No contracts on file.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
