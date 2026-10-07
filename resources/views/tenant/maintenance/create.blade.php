@extends('layouts.tenant')

@section('title', 'Report Maintenance Issue')

@section('content')
<div class="space-y-6 max-w-2xl mx-auto">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Report an Issue / Repair</h1>
            <p class="text-sm text-slate-500">Submit a work order ticket to your landlord for immediate attention.</p>
        </div>
        <a href="{{ route('tenant.maintenance.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-lg transition">
            &larr; Back
        </a>
    </div>

    @if(!$assignment)
        <div class="p-6 bg-amber-50 border border-amber-200 rounded-xl text-amber-900 text-sm">
            <span class="font-bold">Notice:</span> You do not currently have an active assigned room. Please contact your landlord to link your room assignment before filing tickets.
        </div>
    @else
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6">
            <form method="POST" action="{{ route('tenant.maintenance.store') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf

                <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-600 flex justify-between">
                    <div>
                        <span class="font-bold text-slate-700">Reporting for Unit:</span>
                        <span>{{ $assignment->property->name }} &bull; Room {{ $assignment->room->room_number }} (Bed {{ $assignment->bed->bed_number }})</span>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Issue Category *</label>
                        <select name="category" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm bg-white focus:ring-2 focus:ring-indigo-500 font-medium">
                            <option value="plumbing" {{ old('category') === 'plumbing' ? 'selected' : '' }}>Plumbing & Water (Leaks, Clogs)</option>
                            <option value="electrical" {{ old('category') === 'electrical' ? 'selected' : '' }}>Electrical & Lighting (Outlets, Breaker)</option>
                            <option value="internet" {{ old('category') === 'internet' ? 'selected' : '' }}>WiFi & Internet Connection</option>
                            <option value="room" {{ old('category') === 'room' ? 'selected' : '' }}>Room Fixture / Door / Locks</option>
                            <option value="bathroom" {{ old('category') === 'bathroom' ? 'selected' : '' }}>Bathroom / Shower / CR</option>
                            <option value="furniture" {{ old('category') === 'furniture' ? 'selected' : '' }}>Bed / Mattress / Furniture</option>
                            <option value="cleaning" {{ old('category') === 'cleaning' ? 'selected' : '' }}>Cleanliness & Garbage</option>
                            <option value="other" {{ old('category') === 'other' ? 'selected' : '' }}>Other Concern</option>
                        </select>
                        @error('category')<p class="text-xs text-rose-500 mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Urgency Level *</label>
                        <select name="priority" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm bg-white focus:ring-2 focus:ring-indigo-500 font-semibold">
                            <option value="low" {{ old('priority') === 'low' ? 'selected' : '' }}>Low (Minor cosmetic issue)</option>
                            <option value="medium" {{ old('priority', 'medium') === 'medium' ? 'selected' : '' }}>Medium (Needs repair soon)</option>
                            <option value="high" {{ old('priority') === 'high' ? 'selected' : '' }}>High (Affecting daily routine)</option>
                            <option value="urgent" {{ old('priority') === 'urgent' ? 'selected' : '' }}>Urgent (Flooding / electrical hazard)</option>
                        </select>
                        @error('priority')<p class="text-xs text-rose-500 mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Issue Summary / Title *</label>
                    <input type="text" name="title" value="{{ old('title') }}" required placeholder="e.g. Bathroom faucet leaking continuously" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                    @error('title')<p class="text-xs text-rose-500 mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Detailed Description *</label>
                    <textarea name="description" rows="4" required placeholder="Please provide specific details so the landlord or maintenance technician can bring the right tools..." class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 leading-relaxed">{{ old('description') }}</textarea>
                    @error('description')<p class="text-xs text-rose-500 mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Attach Photo (Optional)</label>
                    <input type="file" name="photo" accept="image/*" class="w-full px-3 py-1.5 border border-slate-300 rounded-lg text-xs file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-xs file:bg-slate-100">
                    <p class="text-[11px] text-slate-400 mt-1">Photos help speed up troubleshooting and repair response times.</p>
                </div>

                <div class="flex items-center justify-end space-x-3 pt-4 border-t border-slate-100">
                    <a href="{{ route('tenant.maintenance.index') }}" class="px-4 py-2 text-slate-600 hover:text-slate-800 text-sm font-semibold">Cancel</a>
                    <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm rounded-lg shadow transition">
                        Submit Ticket
                    </button>
                </div>
            </form>
        </div>
    @endif
</div>
@endsection

