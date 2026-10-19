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

    <form method="POST" action="{{ route('owner.rooms.update', $room) }}" enctype="multipart/form-data" class="space-y-4">
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
                    <option value="single"    {{ old('room_type', $room->room_type) === 'single'    ? 'selected' : '' }}>Single</option>
                    <option value="double"    {{ old('room_type', $room->room_type) === 'double'    ? 'selected' : '' }}>Double</option>
                    <option value="shared"    {{ old('room_type', $room->room_type) === 'shared'    ? 'selected' : '' }}>Shared</option>
                    <option value="dormitory" {{ old('room_type', $room->room_type) === 'dormitory' ? 'selected' : '' }}>Dormitory</option>
                    <option value="other"     {{ old('room_type', $room->room_type) === 'other'     ? 'selected' : '' }}>Other</option>
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
                    <option value="available"   {{ old('status', $room->status) === 'available'   ? 'selected' : '' }}>Available</option>
                    <option value="full"        {{ old('status', $room->status) === 'full'        ? 'selected' : '' }}>Full</option>
                    <option value="maintenance" {{ old('status', $room->status) === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                    <option value="inactive"    {{ old('status', $room->status) === 'inactive'    ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <textarea name="description" rows="2"
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500">{{ old('description', $room->description) }}</textarea>
        </div>

        {{-- Room Photo --}}
        <div class="border-t border-gray-200 pt-5" x-data="roomPhotoPreview('{{ $room->photo ? asset('storage/' . $room->photo) : '' }}')">
            <h3 class="font-bold text-gray-900 text-sm mb-1">Room Photo</h3>
            <p class="text-xs text-gray-400 mb-3">Shown on the public room listing. Upload a new photo to replace the current one.</p>

            {{-- Current / Preview --}}
            <div class="relative w-full h-40 rounded-xl overflow-hidden border-2 border-dashed border-gray-300 mb-3 cursor-pointer hover:border-emerald-400 transition"
                 x-on:click="$refs.fileInput.click()"
                 x-on:dragover.prevent x-on:drop.prevent="handleDrop($event)">

                <template x-if="preview">
                    <img :src="preview" class="w-full h-full object-cover">
                </template>
                <template x-if="!preview">
                    <div class="flex flex-col items-center justify-center h-full text-gray-400">
                        <span class="text-4xl mb-1">📷</span>
                        <span class="text-sm font-medium">Click or drag a photo here</span>
                        <span class="text-xs">JPG, PNG, WEBP — max 4MB</span>
                    </div>
                </template>

                {{-- Hover overlay when photo exists --}}
                <template x-if="preview">
                    <div class="absolute inset-0 bg-black/30 opacity-0 hover:opacity-100 transition flex items-center justify-center">
                        <span class="text-white text-sm font-semibold">🔄 Replace Photo</span>
                    </div>
                </template>

                <input type="file" name="photo" accept="image/*" class="hidden"
                       x-ref="fileInput" x-on:change="handleFile($event.target.files[0])">
            </div>

            <div class="flex items-center gap-3">
                <button type="button" @click="$refs.fileInput.click()"
                        class="px-3 py-1.5 text-xs font-medium text-emerald-700 border border-emerald-300 rounded-lg hover:bg-emerald-50 transition">
                    {{ $room->photo ? '🔄 Replace Photo' : '📷 Upload Photo' }}
                </button>

                @if($room->photo)
                    <form method="POST" action="{{ route('owner.rooms.photo.destroy', $room) }}"
                          onsubmit="return confirm('Remove this room photo?')" class="inline">
                        @csrf @method('DELETE')
                        <button type="submit"
                                class="px-3 py-1.5 text-xs font-medium text-red-600 border border-red-300 rounded-lg hover:bg-red-50 transition">
                            ✕ Remove Current Photo
                        </button>
                    </form>
                @endif

                <button x-show="preview && !{{ $room->photo ? 'true' : 'false' }}" type="button" @click="clear()"
                        class="px-3 py-1.5 text-xs font-medium text-gray-500 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                    ✕ Cancel Upload
                </button>
            </div>
        </div>

        <div class="pt-4 flex items-center justify-end gap-3 border-t border-gray-100">
            <a href="{{ route('owner.rooms.show', $room) }}" class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50">Cancel</a>
            <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-semibold shadow-sm">
                Update Room
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
function roomPhotoPreview(existing) {
    return {
        preview: existing || null,
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
