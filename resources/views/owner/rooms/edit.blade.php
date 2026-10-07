@extends('layouts.owner')

@section('page_title', 'Edit Room ' . $room->room_number)

@section('content')
<div class="max-w-2xl mx-auto bg-white rounded-xl border border-gray-200 p-6 shadow-sm">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Edit Room {{ $room->room_number }}</h2>
            <p class="text-sm text-gray-500">{{ $room->property?->name }}</p>
        </div>
        <form method="POST" action="{{ route('owner.rooms.destroy', $room) }}"
              onsubmit="return confirm('Are you sure you want to delete this room?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="text-xs text-red-600 hover:text-red-700 font-semibold p-2">Delete Room</button>
        </form>
    </div>

    @if($errors->any())
        <div class="mb-5 p-4 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('owner.rooms.update', $room) }}" class="space-y-4">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Room Number *</label>
                <input type="text" name="room_number" value="{{ old('room_number', $room->room_number) }}" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Room Label / Name</label>
                <input type="text" name="room_name" value="{{ old('room_name', $room->room_name) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Room Type *</label>
                <select name="room_type" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500">
                    <option value="single" {{ old('room_type', $room->room_type) === 'single' ? 'selected' : '' }}>Single</option>
                    <option value="double" {{ old('room_type', $room->room_type) === 'double' ? 'selected' : '' }}>Double</option>
                    <option value="shared" {{ old('room_type', $room->room_type) === 'shared' ? 'selected' : '' }}>Shared</option>
                    <option value="dormitory" {{ old('room_type', $room->room_type) === 'dormitory' ? 'selected' : '' }}>Dormitory</option>
                    <option value="other" {{ old('room_type', $room->room_type) === 'other' ? 'selected' : '' }}>Other</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Capacity (Beds) *</label>
                <input type="number" name="capacity" value="{{ old('capacity', $room->capacity) }}" min="1" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Monthly Rate (₱) *</label>
                <input type="number" step="0.01" name="rental_rate" value="{{ old('rental_rate', $room->rental_rate) }}" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Floor Level</label>
                <input type="text" name="floor" value="{{ old('floor', $room->floor) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status *</label>
                <select name="status" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500">
                    <option value="available" {{ old('status', $room->status) === 'available' ? 'selected' : '' }}>Available</option>
                    <option value="full" {{ old('status', $room->status) === 'full' ? 'selected' : '' }}>Full</option>
                    <option value="maintenance" {{ old('status', $room->status) === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                    <option value="inactive" {{ old('status', $room->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <textarea name="description" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500">{{ old('description', $room->description) }}</textarea>
        </div>

        <div class="pt-4 flex items-center justify-end gap-3 border-t border-gray-100">
            <a href="{{ route('owner.rooms.show', $room) }}" class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50">Cancel</a>
            <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-semibold shadow-sm">
                Update Room
            </button>
        </div>
    </form>
</div>
@endsection

