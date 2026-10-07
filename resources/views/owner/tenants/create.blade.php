@extends('layouts.owner')

@section('page_title', 'Register New Tenant')

@section('content')
<div class="max-w-3xl mx-auto bg-white rounded-xl border border-gray-200 p-6 shadow-sm" x-data="{ createAccount: false }">
    <div class="mb-6">
        <h2 class="text-xl font-bold text-gray-900">Register New Tenant</h2>
        <p class="text-sm text-gray-500">Record tenant personal details, emergency contacts, and initial bed assignment.</p>
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

    <form method="POST" action="{{ route('owner.tenants.store') }}" class="space-y-6">
        @csrf

        {{-- Personal Info --}}
        <div>
            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">1. Personal Information</h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">First Name *</label>
                    <input type="text" name="first_name" value="{{ old('first_name') }}" required
                           class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Middle Name</label>
                    <input type="text" name="middle_name" value="{{ old('middle_name') }}"
                           class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Last Name *</label>
                    <input type="text" name="last_name" value="{{ old('last_name') }}" required
                           class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-emerald-500">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-3">
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Date of Birth</label>
                    <input type="date" name="date_of_birth" value="{{ old('date_of_birth') }}"
                           class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Gender</label>
                    <select name="gender" class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-emerald-500">
                        <option value="">-- Select --</option>
                        <option value="male" {{ old('gender') === 'male' ? 'selected' : '' }}>Male</option>
                        <option value="female" {{ old('gender') === 'female' ? 'selected' : '' }}>Female</option>
                        <option value="other" {{ old('gender') === 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Contact Number</label>
                    <input type="text" name="contact_number" value="{{ old('contact_number') }}" placeholder="09123456789"
                           class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-emerald-500">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-3">
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Email Address</label>
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="tenant@example.com"
                           class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Permanent Home Address</label>
                    <input type="text" name="address" value="{{ old('address') }}"
                           class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-emerald-500">
                </div>
            </div>
        </div>

        {{-- Emergency Contact --}}
        <div class="border-t border-gray-100 pt-5">
            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">2. Emergency Contact</h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Emergency Contact Person</label>
                    <input type="text" name="emergency_contact" value="{{ old('emergency_contact') }}"
                           class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Relationship</label>
                    <input type="text" name="emergency_relationship" value="{{ old('emergency_relationship') }}" placeholder="e.g. Parent, Sibling"
                           class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Emergency Phone Number</label>
                    <input type="text" name="emergency_contact_number" value="{{ old('emergency_contact_number') }}"
                           class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-emerald-500">
                </div>
            </div>
        </div>

        {{-- Government ID --}}
        <div class="border-t border-gray-100 pt-5">
            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">3. Verification & Government ID</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Government ID Type</label>
                    <input type="text" name="gov_id_type" value="{{ old('gov_id_type') }}" placeholder="e.g. Driver's License, PhilID, Passport, Student ID"
                           class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">ID Number (Encrypted on Storage)</label>
                    <input type="text" name="gov_id_number" value="{{ old('gov_id_number') }}"
                           class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-emerald-500">
                </div>
            </div>
        </div>

        {{-- Bed Assignment --}}
        <div class="border-t border-gray-100 pt-5">
            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">4. Assign Bed Unit (Optional)</h3>
            <p class="text-xs text-gray-500 mb-3">You can immediately assign an available bed or do it later.</p>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Select Available Bed</label>
                <select name="bed_id" class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-emerald-500">
                    <option value="">-- Assign Later --</option>
                    @foreach($properties as $property)
                        <optgroup label="{{ $property->name }} ({{ $property->code }})">
                            @foreach($property->rooms as $room)
                                @foreach($room->beds as $bed)
                                    <option value="{{ $bed->id }}" {{ old('bed_id') == $bed->id ? 'selected' : '' }}>
                                        Room {{ $room->room_number }} – Bed {{ $bed->bed_number }} (₱{{ number_format($bed->rental_rate, 2) }}/mo)
                                    </option>
                                @endforeach
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Tenant Portal Account --}}
        <div class="border-t border-gray-100 pt-5 bg-gray-50 p-4 rounded-xl">
            <label class="flex items-center gap-2 cursor-pointer text-sm font-semibold text-gray-800">
                <input type="checkbox" name="create_user_account" value="1" x-model="createAccount" class="rounded text-emerald-600 focus:ring-emerald-500">
                <span>Create Tenant Portal Login Account</span>
            </label>
            <p class="text-xs text-gray-500 mt-1">Allows tenant to log in, view contracts, check monthly dues, and submit repair requests.</p>

            <div x-show="createAccount" class="mt-3 space-y-2" x-cloak>
                <label class="block text-xs font-medium text-gray-700">Initial Password</label>
                <input type="password" name="password" placeholder="Default: tenant1234"
                       class="w-full sm:w-1/2 px-3 py-2 border rounded-lg text-xs focus:ring-2 focus:ring-emerald-500">
            </div>
        </div>

        <div class="pt-4 flex items-center justify-end gap-3 border-t border-gray-100">
            <a href="{{ route('owner.tenants.index') }}" class="px-4 py-2 border rounded-lg text-sm text-gray-700 hover:bg-gray-50">Cancel</a>
            <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-semibold shadow-sm">
                Register Tenant
            </button>
        </div>
    </form>
</div>
@endsection

