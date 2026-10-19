@extends('layouts.owner')

@section('title', 'Rental Requests — TahananHub')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Rental Requests</h1>
    <span class="text-sm text-gray-500">{{ $requests->total() }} total</span>
</div>

@if($requests->isEmpty())
    <div class="text-center py-20 bg-white rounded-2xl border border-gray-200">
        <div class="text-5xl mb-4">📋</div>
        <h3 class="text-lg font-semibold text-gray-700 mb-2">No rental requests yet</h3>
        <p class="text-gray-500 text-sm">Requests will appear here when tenants apply for your properties.</p>
    </div>
@else
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Applicant</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600 hidden sm:table-cell">Property / Room / Bed</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600 hidden md:table-cell">Move-in</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600 hidden md:table-cell">Rent</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Status</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($requests as $req)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3">
                            <p class="font-semibold text-gray-900">{{ $req->user->name }}</p>
                            <p class="text-gray-400 text-xs">{{ $req->user->email }}</p>
                        </td>
                        <td class="px-4 py-3 hidden sm:table-cell">
                            <p class="font-medium text-gray-800">{{ $req->property->name }}</p>
                            <p class="text-gray-500 text-xs">{{ $req->room->display_name }} · {{ $req->bed->display_name }}</p>
                        </td>
                        <td class="px-4 py-3 hidden md:table-cell text-gray-700">
                            {{ $req->desired_move_in_date->format('M j, Y') }}
                        </td>
                        <td class="px-4 py-3 hidden md:table-cell font-semibold text-indigo-700">
                            ₱{{ number_format($req->monthly_rent, 0) }}
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 text-xs font-bold rounded-full {{ $req->status_badge_class }} uppercase">
                                {{ $req->status }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <a href="{{ route('owner.rental-requests.show', $req) }}"
                               class="text-indigo-600 hover:underline text-xs font-medium">View</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $requests->links() }}</div>
@endif
@endsection
