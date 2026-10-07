@extends('layouts.admin')

@section('page_title', 'Edit House Owner: ' . ($owner->business_name ?? 'Owner #' . $owner->id))

@section('content')
<div class="max-w-2xl mx-auto bg-white rounded-xl border border-gray-200 p-6 shadow-sm">
    <div class="mb-6">
        <h2 class="text-xl font-bold text-gray-900">Update Owner Platform Details</h2>
        <p class="text-sm text-gray-500">Configure business information and subscription plan.</p>
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

    <form method="POST" action="{{ route('admin.owners.update', $owner) }}" class="space-y-4">
        @csrf
        @method('PUT')

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Business Name</label>
            <input type="text" name="business_name" value="{{ old('business_name', $owner->business_name) }}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Business Address</label>
            <textarea name="business_address" rows="2"
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">{{ old('business_address', $owner->business_address) }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Subscription Plan *</label>
            <select name="subscription_plan" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                <option value="free" {{ old('subscription_plan', $owner->subscription_plan) === 'free' ? 'selected' : '' }}>Free</option>
                <option value="basic" {{ old('subscription_plan', $owner->subscription_plan) === 'basic' ? 'selected' : '' }}>Basic</option>
                <option value="standard" {{ old('subscription_plan', $owner->subscription_plan) === 'standard' ? 'selected' : '' }}>Standard</option>
                <option value="premium" {{ old('subscription_plan', $owner->subscription_plan) === 'premium' ? 'selected' : '' }}>Premium</option>
            </select>
        </div>

        <div class="pt-4 flex items-center justify-end gap-3 border-t border-gray-100">
            <a href="{{ route('admin.owners.show', $owner) }}" class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50">Cancel</a>
            <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-semibold">Save Changes</button>
        </div>
    </form>
</div>
@endsection

