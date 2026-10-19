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

    <form method="POST" action="{{ route('owner.rooms.store') }}" enctype="multipart/form-data" class="space-y-4">
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

        {{-- Room Photo --}}
        <div class="border-t border-gray-200 pt-5" x-data="roomPhotoPreview()">
            <h3 class="font-bold text-gray-900 text-sm mb-1">Room Photo</h3>
            <p class="text-xs text-gray-400 mb-3">Upload one photo shown on the public room listing. Optional.</p>

            <label class="flex flex-col items-center justify-center w-full h-28 border-2 border-dashed border-gray-300 rounded-xl cursor-pointer hover:border-emerald-400 hover:bg-emerald-50 transition"
                   x-on:dragover.prevent x-on:drop.prevent="handleDrop($event)">
                <template x-if="!preview">
                    <div class="flex flex-col items-center">
                        <span class="text-3xl mb-1">📷</span>
                        <span class="text-sm font-medium text-gray-600">Click or drag a photo here</span>
                        <span class="text-xs text-gray-400">JPG, PNG, WEBP — max 4MB</span>
                    </div>
                </template>
                <template x-if="preview">
                    <div class="relative w-full h-full">
                        <img :src="preview" class="w-full h-full object-cover rounded-xl">
                        <span class="absolute inset-0 flex items-center justify-center bg-black/30 rounded-xl text-white text-xs font-semibold opacity-0 hover:opacity-100 transition">Change Photo</span>
                    </div>
                </template>
                <input type="file" name="photo" accept="image/*" class="hidden"
                       x-ref="fileInput" x-on:change="handleFile($event.target.files[0])">
            </label>

            <button x-show="preview" type="button" @click="clear()"
                    class="mt-2 text-xs text-red-500 hover:text-red-700 font-medium">
                ✕ Remove photo
            </button>
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

@push('scripts')
<script>
function roomPhotoPreview() {
    return {
        preview: null,
        handleFile(file) {
            if (!file) return;
            const reader = new FileReader();
            reader.onload = e => this.preview = e.target.result;
            reader.readAsDataURL(file);
        },
        handleDrop(e) { this.handleFile(e.dataTransfer.files[0]); },
        clear() {
            this.preview = null;
            this.$refs.fileInput.value = '';
        }
    }
}
</script>
@endpush
@endsection

