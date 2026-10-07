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

    <form method="POST" action="{{ route('owner.properties.update', $property) }}" class="space-y-4">
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

        <div class="pt-4 flex items-center justify-end gap-3 border-t border-gray-100">
            <a href="{{ route('owner.properties.show', $property) }}" class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50">Cancel</a>
            <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-semibold shadow-sm">
                Update Property
            </button>
        </div>
    </form>
</div>
@endsection

