@extends('layouts.owner')

@section('page_title', 'Edit Property: ' . $property->name)

@section('content')
<div class="max-w-2xl mx-auto bg-white rounded-xl border border-gray-200 p-6 shadow-sm">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Edit Property</h2>
            <p class="text-sm text-gray-500">Update property details, address, or operational status.</p>
        </div>
        <form method="POST" action="{{ route('owner.properties.destroy', $property) }}"
              onsubmit="return confirm('Are you sure you want to delete this property? This cannot be undone.');">
            @csrf
            @method('DELETE')
            <button type="submit" class="text-xs text-red-600 hover:text-red-700 font-semibold p-2">Delete Property</button>
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

    <form method="POST" action="{{ route('owner.properties.update', $property) }}" enctype="multipart/form-data" class="space-y-4">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Property Name *</label>
                <input type="text" name="name" value="{{ old('name', $property->name) }}" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Property Code *</label>
                <input type="text" name="code" value="{{ old('code', $property->code) }}" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 font-mono">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Street Address *</label>
            <input type="text" name="address" value="{{ old('address', $property->address) }}" required
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500">
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Barangay *</label>
                <input type="text" name="barangay" value="{{ old('barangay', $property->barangay) }}" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Municipality / City *</label>
                <input type="text" name="municipality" value="{{ old('municipality', $property->municipality) }}" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Province *</label>
                <input type="text" name="province" value="{{ old('province', $property->province) }}" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Contact Number</label>
                <input type="text" name="contact_number" value="{{ old('contact_number', $property->contact_number) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status *</label>
                <select name="status" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500">
                    <option value="active" {{ old('status', $property->status) === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ old('status', $property->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    <option value="under_maintenance" {{ old('status', $property->status) === 'under_maintenance' ? 'selected' : '' }}>Under Maintenance</option>
                </select>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Description / Notes</label>
            <textarea name="description" rows="3"
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500">{{ old('description', $property->description) }}</textarea>
        </div>

        {{-- Property Photos --}}
        <div class="border-t border-gray-200 pt-5" x-data="photoPreview()">
            <h3 class="font-bold text-gray-900 text-sm mb-1">Property Photos</h3>
            <p class="text-xs text-gray-400 mb-3">Upload additional photos. Manage existing photos from the property page. First new upload becomes cover if none set.</p>

            {{-- Existing photos strip --}}
            @php $photos = $property->photos; @endphp
            @if($photos->isNotEmpty())
                <div class="flex gap-2 overflow-x-auto pb-2 mb-3">
                    @foreach($photos as $photo)
                        <div class="relative flex-shrink-0 w-20 h-16 rounded-lg overflow-hidden border-2 {{ $photo->is_cover ? 'border-emerald-500' : 'border-gray-200' }}">
                            <img src="{{ asset('storage/' . $photo->path) }}" class="w-full h-full object-cover">
                            @if($photo->is_cover)
                                <span class="absolute bottom-0 left-0 right-0 text-center bg-emerald-600/80 text-white text-[9px] font-bold py-0.5">COVER</span>
                            @endif
                        </div>
                    @endforeach
                    <a href="{{ route('owner.properties.show', $property) }}#photos"
                       class="flex-shrink-0 w-20 h-16 rounded-lg border-2 border-dashed border-gray-300 flex flex-col items-center justify-center text-gray-400 hover:border-emerald-400 hover:text-emerald-600 transition text-xs">
                        <span class="text-lg">⚙️</span>
                        <span>Manage</span>
                    </a>
                </div>
            @endif

            {{-- Upload new photos --}}
            <label class="flex flex-col items-center justify-center w-full h-28 border-2 border-dashed border-gray-300 rounded-xl cursor-pointer hover:border-emerald-400 hover:bg-emerald-50 transition"
                   x-on:dragover.prevent x-on:drop.prevent="handleDrop($event)">
                <span class="text-2xl mb-1">📷</span>
                <span class="text-sm font-medium text-gray-600">Click or drag to add more photos</span>
                <span class="text-xs text-gray-400">JPG, PNG, WEBP — max 4MB each</span>
                <input type="file" name="photos[]" multiple accept="image/*" class="hidden"
                       x-ref="fileInput" x-on:change="handleFiles($event.target.files)">
            </label>

            <div class="grid grid-cols-3 sm:grid-cols-5 gap-2 mt-3" x-show="previews.length > 0">
                <template x-for="(src, i) in previews" :key="i">
                    <div class="relative group rounded-lg overflow-hidden border border-gray-200 h-20">
                        <img :src="src" class="w-full h-full object-cover">
                        <span x-show="i === 0 && {{ $photos->isEmpty() ? 'true' : 'false' }}" class="absolute top-1 left-1 px-1 py-0.5 bg-emerald-600 text-white text-[9px] font-bold rounded">COVER</span>
                        <button type="button" @click="remove(i)"
                                class="absolute top-1 right-1 w-5 h-5 bg-red-500 text-white rounded-full text-xs opacity-0 group-hover:opacity-100 transition flex items-center justify-center">✕</button>
                    </div>
                </template>
            </div>
        </div>

        {{-- Marketplace / Listing Settings --}}
        <div class="border-t border-gray-200 pt-5 mt-2">
            <h3 class="font-bold text-gray-900 text-sm mb-4">🏪 Public Marketplace Listing</h3>

            <div class="flex items-center justify-between p-4 bg-emerald-50 border border-emerald-200 rounded-xl mb-4">
                <div>
                    <p class="font-semibold text-gray-900 text-sm">Publish House Listing</p>
                    <p class="text-xs text-gray-500">Show this property on the public TahananHub marketplace.</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="is_listed" value="1" class="sr-only peer"
                           {{ old('is_listed', $property->is_listed) ? 'checked' : '' }}>
                    <div class="w-11 h-6 bg-gray-300 peer-focus:ring-2 peer-focus:ring-emerald-400 rounded-full peer peer-checked:bg-emerald-600 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full"></div>
                </label>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Public Contact Number</label>
                    <input type="text" name="public_contact" value="{{ old('public_contact', $property->public_contact) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500"
                           placeholder="Shown to visitors on the listing">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Latitude</label>
                        <input type="number" step="any" name="latitude" value="{{ old('latitude', $property->latitude) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500"
                               placeholder="14.2833">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Longitude</label>
                        <input type="number" step="any" name="longitude" value="{{ old('longitude', $property->longitude) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500"
                               placeholder="121.1167">
                    </div>
                </div>
            </div>

            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Public Description</label>
                <textarea name="public_description" rows="3"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500"
                          placeholder="Describe your property for potential tenants...">{{ old('public_description', $property->public_description) }}</textarea>
            </div>

            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Amenities</label>
                <p class="text-xs text-gray-400 mb-2">Enter one amenity per line (e.g. WiFi, Parking, Laundry)</p>
                <textarea name="amenities_text" rows="4"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500"
                          placeholder="WiFi&#10;Parking&#10;Laundry&#10;Common Kitchen">{{ old('amenities_text', is_array($property->amenities) ? implode("\n", $property->amenities) : '') }}</textarea>
            </div>

            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">House Rules</label>
                <textarea name="house_rules" rows="4"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500"
                          placeholder="No smoking inside.&#10;Curfew at 11 PM.">{{ old('house_rules', $property->house_rules) }}</textarea>
            </div>
        </div>

        <div class="pt-4 flex items-center justify-end gap-3 border-t border-gray-100">
            <a href="{{ route('owner.properties.show', $property) }}" class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50">Cancel</a>
            <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-semibold shadow-sm">
                Update Property
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
function photoPreview() {
    return {
        previews: [],
        files: [],
        handleFiles(fileList) {
            const newFiles = Array.from(fileList).slice(0, 10 - this.files.length);
            newFiles.forEach(f => {
                this.files.push(f);
                const reader = new FileReader();
                reader.onload = e => this.previews.push(e.target.result);
                reader.readAsDataURL(f);
            });
            this.syncInput();
        },
        handleDrop(e) { this.handleFiles(e.dataTransfer.files); },
        remove(i) {
            this.previews.splice(i, 1);
            this.files.splice(i, 1);
            this.syncInput();
        },
        syncInput() {
            const dt = new DataTransfer();
            this.files.forEach(f => dt.items.add(f));
            this.$refs.fileInput.files = dt.files;
        }
    }
}
</script>
@endpush
@endsection

