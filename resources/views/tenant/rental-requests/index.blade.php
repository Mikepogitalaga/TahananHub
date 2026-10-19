@extends('layouts.tenant')

@section('title', 'My Rental Requests — TahananHub')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-gray-900">My Rental Requests</h1>
    <a href="{{ route('houses.index') }}" class="px-4 py-2 text-sm font-medium text-indigo-700 border border-indigo-300 rounded-lg hover:bg-indigo-50 transition">
        Browse Houses
    </a>
</div>

@if($requests->isEmpty())
    <div class="text-center py-20 bg-white rounded-2xl border border-gray-200">
        <div class="text-5xl mb-4">📋</div>
        <h3 class="text-lg font-semibold text-gray-700 mb-2">No rental requests yet</h3>
        <p class="text-gray-500 text-sm mb-4">Browse available houses and submit a request.</p>
        <a href="{{ route('houses.index') }}" class="px-5 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition">
            Browse Houses
        </a>
    </div>
@else
    <div class="space-y-4">
        @foreach($requests as $req)
            <div class="bg-white rounded-2xl border border-gray-200 p-5 hover:shadow-sm transition">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex-1 min-w-0">
                        <p class="font-bold text-gray-900 text-base">{{ $req->property->name ?? '—' }}</p>
                        <p class="text-gray-500 text-sm">{{ $req->room->display_name ?? '—' }} · {{ $req->bed->display_name ?? '—' }}</p>
                        <p class="text-indigo-700 font-semibold text-sm mt-1">₱{{ number_format($req->monthly_rent, 0) }}/month</p>
                        <p class="text-gray-500 text-xs mt-1">Move-in: {{ $req->desired_move_in_date->format('F j, Y') }}</p>
                        <p class="text-gray-400 text-xs">Submitted: {{ $req->created_at->format('M j, Y') }}</p>
                    </div>
                    <div class="flex flex-col items-end gap-2">
                        <span class="px-3 py-1 text-xs font-bold rounded-full {{ $req->status_badge_class }} uppercase">
                            {{ $req->status }}
                        </span>
                        <a href="{{ route('tenant.rental-requests.show', $req) }}"
                           class="text-xs text-indigo-600 hover:underline">View →</a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-6">{{ $requests->links() }}</div>
@endif
@endsection
