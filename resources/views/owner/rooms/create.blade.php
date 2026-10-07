@extends('layouts.owner')

@section('page_title', 'Add Room')

@section('content')
<div class="max-w-2xl mx-auto bg-white rounded-xl border border-gray-200 p-6 shadow-sm">
    <div class="mb-6">
        <h2 class="text-xl font-bold text-gray-900">Add New Room</h2>
        <p class="text-sm text-gray-500">Configure room number, rental rates, and bed capacity.</p>
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

    <form method="POST" action="{{ route('owner.rooms.store') }}" class="space-y-4">
        @csrf

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Select Property *</label>
            <select name="property_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500">
                <option value="">-- Choose Property --</option>
                @foreach($properties as $prop)
                    <option value="{{ $prop->id }}" {{ (old('property_id', $selectedPropertyId) == $prop->id) ? 'selected' : '' }}>
                        {{ $prop->name }} ({{ $prop->code }})
                    </option>
                @endforeach
            </select>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Room Number *</label>
                <input type="text" name="room_number" value="{{ old('room_number') }}" required
                       placeholder="e.g. 101, 2A"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Room Label / Name</label>
                <input type="text" name="room_name" value="{{ old('room_name') }}"
                       placeholder="e.g. Master Bedroom, Corner Room"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Room Type *</label>
                <select name="room_type" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500">
                    <option value="single" {{ old('room_type') === 'single' ? 'selected' : '' }}>Single</option>
                    <option value="double" {{ old('room_type') === 'double' ? 'selected' : '' }}>Double</option>
                    <option value="shared" {{ old('room_type', 'shared') === 'shared' ? 'selected' : '' }}>Shared</option>
                    <option value="dormitory" {{ old('room_type') === 'dormitory' ? 'selected' : '' }}>Dormitory</option>
                    <option value="other" {{ old('room_type') === 'other' ? 'selected' : '' }}>Other</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Capacity (Beds) *</label>
                <input type="number" name="capacity" value="{{ old('capacity', 2) }}" min="1" max="20" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Monthly Rate (₱) *</label>
                <input type="number" step="0.01" name="rental_rate" value="{{ old('rental_rate', 3000) }}" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Floor Level</label>
                <input type="text" name="floor" value="{{ old('floor') }}" placeholder="e.g. 2nd Floor"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status *</label>
                <select name="status" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500">
                    <option value="available" {{ old('status', 'available') === 'available' ? 'selected' : '' }}>Available</option>
                    <option value="full" {{ old('status') === 'full' ? 'selected' : '' }}>Full</option>
                    <option value="maintenance" {{ old('status') === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                    <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <textarea name="description" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500">{{ old('description') }}</textarea>
        </div>

        <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-lg">
            <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-emerald-900">
                <input type="checkbox" name="auto_generate_beds" value="1" checked class="rounded text-emerald-600 focus:ring-emerald-500">
                <span>Automatically generate bed units (Bed A, Bed B...) based on capacity</span>
            </label>
        </div>

        <div class="pt-4 flex items-center justify-end gap-3 border-t border-gray-100">
            <a href="{{ route('owner.rooms.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50">Cancel</a>
            <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-semibold shadow-sm">
                Save Room
            </button>
        </div>
    </form>
</div>
@endsection

