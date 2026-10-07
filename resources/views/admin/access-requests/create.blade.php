@extends('layouts.admin')

@section('page_title', 'Request Access to House Owner Data')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <div class="bg-slate-900 rounded-xl p-6 text-white shadow-md">
        <h2 class="text-xl font-bold flex items-center gap-2 text-amber-400">
            <span>🛡️</span> Zero-Trust Privacy Access Protocol
        </h2>
        <p class="text-xs text-slate-300 mt-2 leading-relaxed">
            In accordance with platform security policies, Super Admins must specify a concrete business reason, request the minimum required permissions, and wait for House Owner authorization before any private data is decrypted or unlocked.
        </p>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm">
        @if($errors->any())
            <div class="mb-5 p-4 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.access-requests.store') }}" class="space-y-6">
            @csrf

            {{-- 1. Select Owner --}}
            <div>
                <label class="block text-sm font-bold text-gray-800 mb-1">Select House Owner Business *</label>
                <select name="house_owner_id" required class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                    <option value="">-- Choose House Owner Workspace --</option>
                    @foreach($owners as $owner)
                        <option value="{{ $owner->id }}" {{ (old('house_owner_id', $selectedOwnerId) == $owner->id) ? 'selected' : '' }}>
                            {{ $owner->business_name ?? 'Owner #' . $owner->id }} ({{ $owner->user?->name }} – {{ $owner->user?->email }})
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- 2. Select Granular Permissions --}}
            <div>
                <label class="block text-sm font-bold text-gray-800 mb-2">
                    Granular Access Permissions Requested *
                </label>
                <p class="text-xs text-gray-500 mb-3">Select ONLY the specific records necessary for your investigation or support task.</p>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 bg-gray-50 p-4 rounded-xl border border-gray-200">
                    @foreach($availablePermissions as $key => $label)
                        <label class="flex items-start gap-2.5 p-2 rounded-lg hover:bg-white transition cursor-pointer text-xs">
                            <input type="checkbox" name="permissions[]" value="{{ $key }}"
                                   {{ is_array(old('permissions')) && in_array($key, old('permissions')) ? 'checked' : '' }}
                                   class="mt-0.5 rounded text-blue-600 focus:ring-blue-500 border-gray-300">
                            <div>
                                <span class="font-bold text-gray-900 block font-mono">{{ $key }}</span>
                                <span class="text-gray-500">{{ $label }}</span>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- 3. Duration --}}
            <div>
                <label class="block text-sm font-bold text-gray-800 mb-1">Temporary Duration *</label>
                <select name="requested_duration_minutes" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                    <option value="15" {{ old('requested_duration_minutes') == 15 ? 'selected' : '' }}>15 Minutes</option>
                    <option value="30" {{ old('requested_duration_minutes') == 30 ? 'selected' : '' }}>30 Minutes</option>
                    <option value="60" {{ old('requested_duration_minutes', 60) == 60 ? 'selected' : '' }}>1 Hour (Recommended)</option>
                    <option value="240" {{ old('requested_duration_minutes') == 240 ? 'selected' : '' }}>4 Hours</option>
                </select>
                <p class="text-[11px] text-gray-400 mt-1">Access automatically expires upon timer completion.</p>
            </div>

            {{-- 4. Reason --}}
            <div>
                <label class="block text-sm font-bold text-gray-800 mb-1">Reason for Access *</label>
                <textarea name="reason" rows="3" required
                          placeholder="Explain clearly to the House Owner why you need temporary access to their private data (e.g. Account investigation, Billing reconciliation support, Technical bug audit)..."
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">{{ old('reason') }}</textarea>
            </div>

            <div class="pt-4 flex items-center justify-end gap-3 border-t border-gray-100">
                <a href="{{ route('admin.access-requests.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50">Cancel</a>
                <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-bold shadow-sm">
                    Submit Access Request
                </button>
            </div>
        </form>
    </div>

</div>
@endsection

