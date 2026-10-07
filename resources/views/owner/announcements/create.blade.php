@extends('layouts.owner')

@section('title', 'Post Announcement')

@section('content')
<div class="space-y-6 max-w-2xl mx-auto">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Post New Announcement</h1>
            <p class="text-sm text-slate-500">Broadcast important updates to your tenants.</p>
        </div>
        <a href="{{ route('owner.announcements.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-lg transition">
            &larr; Back
        </a>
    </div>

    <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
        <form method="POST" action="{{ route('owner.announcements.store') }}" class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Target Property</label>
                <select name="property_id" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm bg-white focus:ring-2 focus:ring-indigo-500">
                    <option value="">-- All My Properties (Broadcast to all tenants) --</option>
                    @foreach($properties as $prop)
                        <option value="{{ $prop->id }}" {{ old('property_id') == $prop->id ? 'selected' : '' }}>
                            {{ $prop->name }}
                        </option>
                    @endforeach
                </select>
                @error('property_id')<p class="text-xs text-rose-500 mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Subject / Headline *</label>
                <input type="text" name="title" value="{{ old('title') }}" required placeholder="e.g. Scheduled Water Interruption on Saturday 9 AM - 2 PM" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                @error('title')<p class="text-xs text-rose-500 mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Notice Content *</label>
                <textarea name="content" rows="6" required placeholder="Write the full message details..." class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 leading-relaxed">{{ old('content') }}</textarea>
                @error('content')<p class="text-xs text-rose-500 mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Status *</label>
                    <select name="status" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm bg-white focus:ring-2 focus:ring-indigo-500 font-semibold">
                        <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active (Publish Immediately)</option>
                        <option value="draft" {{ old('status') === 'draft' ? 'selected' : '' }}>Draft (Hidden)</option>
                        <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Expires On (Optional)</label>
                    <input type="datetime-local" name="expires_at" value="{{ old('expires_at') }}" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div class="flex items-center justify-end space-x-3 pt-4 border-t border-slate-100">
                <a href="{{ route('owner.announcements.index') }}" class="px-4 py-2 text-slate-600 hover:text-slate-800 text-sm font-semibold">Cancel</a>
                <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm rounded-lg shadow transition">
                    Publish Announcement
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

