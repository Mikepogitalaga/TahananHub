@extends('layouts.owner')

@section('page_title', 'Edit Tenant: ' . $tenant->full_name)

@section('content')
<div class="max-w-3xl mx-auto bg-white rounded-xl border border-gray-200 p-6 shadow-sm">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Edit Tenant Profile</h2>
            <p class="text-sm text-gray-500">{{ $tenant->full_name }}</p>
        </div>
        <form method="POST" action="{{ route('owner.tenants.destroy', $tenant) }}"
              onsubmit="return confirm('Delete tenant profile? Cannot be undone.');">
            @csrf
            @method('DELETE')
            <button type="submit" class="text-xs text-red-600 hover:text-red-700 font-semibold p-2">Delete Tenant</button>
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

    <form method="POST" action="{{ route('owner.tenants.update', $tenant) }}" class="space-y-6">
        @csrf
        @method('PUT')

        {{-- Personal Info --}}
        <div>
            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Personal Information</h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">First Name *</label>
                    <input type="text" name="first_name" value="{{ old('first_name', $tenant->first_name) }}" required
                           class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Middle Name</label>
                    <input type="text" name="middle_name" value="{{ old('middle_name', $tenant->middle_name) }}"
                           class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Last Name *</label>
                    <input type="text" name="last_name" value="{{ old('last_name', $tenant->last_name) }}" required
                           class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-emerald-500">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-3">
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Date of Birth</label>
                    <input type="date" name="date_of_birth" value="{{ old('date_of_birth', $tenant->date_of_birth?->format('Y-m-d')) }}"
                           class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Gender</label>
                    <select name="gender" class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-emerald-500">
                        <option value="">-- Select --</option>
                        <option value="male" {{ old('gender', $tenant->gender) === 'male' ? 'selected' : '' }}>Male</option>
                        <option value="female" {{ old('gender', $tenant->gender) === 'female' ? 'selected' : '' }}>Female</option>
                        <option value="other" {{ old('gender', $tenant->gender) === 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Status *</label>
                    <select name="status" required class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-emerald-500">
                        <option value="active" {{ old('status', $tenant->status) === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="moved_out" {{ old('status', $tenant->status) === 'moved_out' ? 'selected' : '' }}>Moved Out</option>
                        <option value="inactive" {{ old('status', $tenant->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="suspended" {{ old('status', $tenant->status) === 'suspended' ? 'selected' : '' }}>Suspended</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-3">
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Contact Number</label>
                    <input type="text" name="contact_number" value="{{ old('contact_number', $tenant->contact_number) }}"
                           class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Email Address</label>
                    <input type="email" name="email" value="{{ old('email', $tenant->email) }}"
                           class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-emerald-500">
                </div>
            </div>
        </div>

        {{-- Emergency Contact --}}
        <div class="border-t border-gray-100 pt-5">
            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Emergency Contact</h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Contact Person</label>
                    <input type="text" name="emergency_contact" value="{{ old('emergency_contact', $tenant->emergency_contact) }}"
                           class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Relationship</label>
                    <input type="text" name="emergency_relationship" value="{{ old('emergency_relationship', $tenant->emergency_relationship) }}"
                           class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Emergency Phone</label>
                    <input type="text" name="emergency_contact_number" value="{{ old('emergency_contact_number', $tenant->emergency_contact_number) }}"
                           class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-emerald-500">
                </div>
            </div>
        </div>

        {{-- Government ID --}}
        <div class="border-t border-gray-100 pt-5">
            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Verification & Government ID</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Government ID Type</label>
                    <input type="text" name="gov_id_type" value="{{ old('gov_id_type', $tenant->gov_id_type) }}"
                           class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">ID Number (Enter to update)</label>
                    <input type="text" name="gov_id_number" placeholder="Encrypted on storage"
                           class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-emerald-500">
                </div>
            </div>
        </div>

        <div class="pt-4 flex items-center justify-end gap-3 border-t border-gray-100">
            <a href="{{ route('owner.tenants.show', $tenant) }}" class="px-4 py-2 border rounded-lg text-sm text-gray-700 hover:bg-gray-50">Cancel</a>
            <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-semibold shadow-sm">
                Save Changes
            </button>
        </div>
    </form>
</div>
@endsection

