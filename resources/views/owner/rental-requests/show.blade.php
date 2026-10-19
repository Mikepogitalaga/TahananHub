@extends('layouts.owner')

@section('title', 'Rental Request — TahananHub')

@section('content')
<div class="max-w-2xl mx-auto">

    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('owner.rental-requests.index') }}" class="text-indigo-600 hover:underline text-sm">← Rental Requests</a>
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

        {{-- Applicant --}}
        <div class="bg-gray-50 rounded-xl p-4 mb-5">
            <p class="text-xs font-semibold text-gray-500 mb-1">Applicant</p>
            <p class="font-bold text-gray-900">{{ $rentalRequest->user->name }}</p>
            <p class="text-gray-500 text-sm">{{ $rentalRequest->user->email }}</p>
            @if($rentalRequest->user->phone)
                <p class="text-gray-500 text-sm">{{ $rentalRequest->user->phone }}</p>
            @endif
        </div>

        {{-- Details --}}
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
            <div class="bg-gray-50 rounded-xl p-4 mb-5">
                <p class="text-xs font-semibold text-gray-500 mb-1">Applicant's Message</p>
                <p class="text-gray-700 text-sm">{{ $rentalRequest->message }}</p>
            </div>
        @endif

        @if($rentalRequest->owner_response)
            <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-4 mb-5">
                <p class="text-xs font-semibold text-indigo-600 mb-1">Your Response</p>
                <p class="text-gray-700 text-sm">{{ $rentalRequest->owner_response }}</p>
                @if($rentalRequest->responded_at)
                    <p class="text-gray-400 text-xs mt-1">{{ $rentalRequest->responded_at->format('M j, Y g:i A') }}</p>
                @endif
            </div>
        @endif

        @if($rentalRequest->isPending())
            <div class="border-t border-gray-200 pt-6 space-y-4" x-data="{ action: null }">
                <p class="text-sm font-semibold text-gray-700">Respond to this request:</p>

                <textarea x-ref="response" name="owner_response" rows="3"
                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm resize-none"
                          placeholder="Optional message to the applicant..."></textarea>

                <div class="flex gap-3">
                    <form method="POST" action="{{ route('owner.rental-requests.approve', $rentalRequest) }}" class="flex-1"
                          onsubmit="return confirm('Approve this request and reserve the bed?')">
                        @csrf
                        <input type="hidden" name="owner_response" x-bind:value="$refs.response.value">
                        <button type="submit"
                                class="w-full px-4 py-3 text-sm font-bold text-white bg-green-600 hover:bg-green-700 rounded-lg transition">
                            ✓ Approve
                        </button>
                    </form>

                    <form method="POST" action="{{ route('owner.rental-requests.reject', $rentalRequest) }}" class="flex-1"
                          onsubmit="return confirm('Reject this request?')">
                        @csrf
                        <input type="hidden" name="owner_response" x-bind:value="$refs.response.value">
                        <button type="submit"
                                class="w-full px-4 py-3 text-sm font-bold text-white bg-red-600 hover:bg-red-700 rounded-lg transition">
                            ✗ Reject
                        </button>
                    </form>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
