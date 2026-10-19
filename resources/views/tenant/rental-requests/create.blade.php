@extends('layouts.tenant')

@section('title', 'Request to Rent — TahananHub')

@section('content')
<div class="max-w-2xl mx-auto">

    <nav class="text-sm text-gray-500 mb-6 flex items-center gap-2 flex-wrap">
        <a href="{{ route('houses.index') }}" class="hover:text-indigo-600">Browse Houses</a>
        <span>/</span>
        <a href="{{ route('houses.show', $property) }}" class="hover:text-indigo-600">{{ $property->name }}</a>
        <span>/</span>
        <a href="{{ route('houses.room', [$property, $room]) }}" class="hover:text-indigo-600">{{ $room->display_name }}</a>
        <span>/</span>
        <span class="text-gray-800 font-medium">Request to Rent</span>
    </nav>

    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 sm:p-8">
        <h1 class="text-2xl font-bold text-gray-900 mb-1">Request to Rent</h1>
        <p class="text-gray-500 text-sm mb-6">Review the details below and submit your request to the owner.</p>

        {{-- Summary --}}
        <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-4 mb-6 space-y-2 text-sm">
            <div class="flex justify-between">
                <span class="text-gray-600">Property</span>
                <span class="font-semibold text-gray-900">{{ $property->name }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600">Room</span>
                <span class="font-semibold text-gray-900">{{ $room->display_name }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600">Bed</span>
                <span class="font-semibold text-gray-900">{{ $bed->display_name }}</span>
            </div>
            <div class="flex justify-between border-t border-indigo-200 pt-2 mt-2">
                <span class="text-gray-600">Monthly Rent</span>
                <span class="font-bold text-indigo-700 text-base">₱{{ number_format($bed->rental_rate, 0) }}</span>
            </div>
        </div>

        <form method="POST" action="{{ route('tenant.rental-requests.store') }}" class="space-y-5">
            @csrf
            <input type="hidden" name="property_id" value="{{ $property->id }}">
            <input type="hidden" name="room_id" value="{{ $room->id }}">
            <input type="hidden" name="bed_id" value="{{ $bed->id }}">

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Desired Move-in Date <span class="text-red-500">*</span></label>
                <input type="date" name="desired_move_in_date" value="{{ old('desired_move_in_date') }}"
                       min="{{ now()->toDateString() }}" required
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm @error('desired_move_in_date') border-red-400 @enderror">
                @error('desired_move_in_date')
                    <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Message to Owner <span class="text-gray-400">(optional)</span></label>
                <textarea name="message" rows="4"
                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm resize-none @error('message') border-red-400 @enderror"
                          placeholder="Introduce yourself or ask any questions...">{{ old('message') }}</textarea>
                @error('message')
                    <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex gap-3 pt-2">
                <a href="{{ route('houses.room', [$property, $room]) }}"
                   class="flex-1 text-center px-4 py-3 text-sm font-medium text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                    Cancel
                </a>
                <button type="submit"
                        class="flex-1 px-4 py-3 text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition shadow-sm">
                    Submit Rental Request
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
