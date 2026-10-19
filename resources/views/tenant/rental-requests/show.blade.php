@extends('layouts.tenant')

@section('title', 'Rental Request — TahananHub')

@section('content')
<div class="max-w-2xl mx-auto">

    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('tenant.rental-requests.index') }}" class="text-indigo-600 hover:underline text-sm">← My Requests</a>
    </div>

    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 sm:p-8">

        <div class="flex items-start justify-between gap-4 mb-6">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Rental Request</h1>
                <p class="text-gray-400 text-xs mt-1">{{ $rentalRequest->request_number }}</p>
            </div>
            <span class="px-3 py-1 text-sm font-bold rounded-full {{ $rentalRequest->status_badge_class }} uppercase">
                {{ $rentalRequest->status }}
            </span>
        </div>

        <div class="space-y-3 text-sm mb-6">
            <div class="flex justify-between py-2 border-b border-gray-100">
                <span class="text-gray-500">Property</span>
                <span class="font-semibold text-gray-900">{{ $rentalRequest->property->name }}</span>
            </div>
            <div class="flex justify-between py-2 border-b border-gray-100">
                <span class="text-gray-500">Room</span>
                <span class="font-semibold text-gray-900">{{ $rentalRequest->room->display_name }}</span>
            </div>
            <div class="flex justify-between py-2 border-b border-gray-100">
                <span class="text-gray-500">Bed</span>
                <span class="font-semibold text-gray-900">{{ $rentalRequest->bed->display_name }}</span>
            </div>
            <div class="flex justify-between py-2 border-b border-gray-100">
                <span class="text-gray-500">Monthly Rent</span>
                <span class="font-bold text-indigo-700">₱{{ number_format($rentalRequest->monthly_rent, 0) }}</span>
            </div>
            <div class="flex justify-between py-2 border-b border-gray-100">
                <span class="text-gray-500">Desired Move-in</span>
                <span class="font-semibold text-gray-900">{{ $rentalRequest->desired_move_in_date->format('F j, Y') }}</span>
            </div>
            <div class="flex justify-between py-2 border-b border-gray-100">
                <span class="text-gray-500">Submitted</span>
                <span class="text-gray-700">{{ $rentalRequest->created_at->format('M j, Y g:i A') }}</span>
            </div>
        </div>

        @if($rentalRequest->message)
            <div class="bg-gray-50 rounded-xl p-4 mb-4">
                <p class="text-xs font-semibold text-gray-500 mb-1">Your Message</p>
                <p class="text-gray-700 text-sm">{{ $rentalRequest->message }}</p>
            </div>
        @endif

        @if($rentalRequest->owner_response)
            <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-4 mb-4">
                <p class="text-xs font-semibold text-indigo-600 mb-1">Owner's Response</p>
                <p class="text-gray-700 text-sm">{{ $rentalRequest->owner_response }}</p>
                @if($rentalRequest->responded_at)
                    <p class="text-gray-400 text-xs mt-1">{{ $rentalRequest->responded_at->format('M j, Y g:i A') }}</p>
                @endif
            </div>
        @endif

        @if($rentalRequest->isPending())
            <form method="POST" action="{{ route('tenant.rental-requests.cancel', $rentalRequest) }}"
                  onsubmit="return confirm('Withdraw this rental request?')">
                @csrf
                @method('PATCH')
                <button type="submit"
                        class="w-full px-4 py-3 text-sm font-medium text-red-600 border border-red-300 rounded-lg hover:bg-red-50 transition">
                    Withdraw Request
                </button>
            </form>
        @endif
    </div>
</div>
@endsection
