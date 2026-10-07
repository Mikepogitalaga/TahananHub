@extends('layouts.owner')

@section('page_title', 'Manage Beds in Room ' . $room->room_number)

@section('content')
<div class="space-y-6">

    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <a href="{{ route('owner.properties.show', $room->property_id) }}" class="text-xs text-emerald-600 hover:underline mb-1 inline-block">
                ← Back to {{ $room->property?->name }}
            </a>
            <h2 class="text-2xl font-bold text-gray-900">Room {{ $room->room_number }} Bed Units</h2>
            <p class="text-xs text-gray-500">
                Property: {{ $room->property?->name }} | Capacity: {{ $room->capacity }} beds | Rate: ₱{{ number_format($room->rental_rate, 2) }}/mo
            </p>
        </div>
        <div>
            <a href="{{ route('owner.rooms.edit', $room) }}" class="px-3 py-1.5 border border-gray-300 rounded-lg text-xs font-semibold text-gray-700 hover:bg-gray-50">
                Edit Room Settings
            </a>
        </div>
    </div>

    {{-- Beds Grid & Add Bed Form --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Left 2 cols: Bed Units --}}
        <div class="lg:col-span-2 space-y-4">
            <h3 class="font-bold text-gray-900 text-sm">Configured Bed Units ({{ $room->beds->count() }})</h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @forelse($room->beds as $bed)
                    @php
                        $assignment = $bed->currentAssignment;
                        $tenant = $assignment?->tenant;
                    @endphp
                    <div class="p-4 rounded-xl border bg-white shadow-sm flex flex-col justify-between
                        {{ $bed->status === 'occupied' ? 'border-emerald-300' : ($bed->status === 'available' ? 'border-blue-300' : 'border-gray-200') }}">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="font-mono font-bold text-base text-gray-900">
                                    🛏️ Bed {{ $bed->bed_number }}
                                </span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase
                                    {{ $bed->status === 'occupied' ? 'bg-emerald-100 text-emerald-800' : ($bed->status === 'available' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800') }}">
                                    {{ $bed->status }}
                                </span>
                            </div>

                            <p class="text-xs text-gray-500 mb-2">
                                Rate: <strong class="text-gray-800">₱{{ number_format($bed->rental_rate ?? $room->rental_rate, 2) }}/mo</strong>
                            </p>

                            @if($tenant)
                                <div class="p-2.5 bg-emerald-50 rounded-lg border border-emerald-200 text-xs mb-3">
                                    <span class="text-[10px] uppercase font-bold text-emerald-700 block">Occupied by:</span>
                                    <strong class="text-emerald-950 font-bold block">{{ $tenant->full_name }}</strong>
                                    <span class="text-[11px] text-emerald-800">Assigned: {{ $assignment->assigned_at->format('M d, Y') }}</span>
                                </div>
                            @else
                                <div class="p-2.5 bg-gray-50 rounded-lg text-xs text-gray-400 mb-3 italic">
                                    No active tenant assigned to this bed.
                                </div>
                            @endif
                        </div>

                        {{-- Bed Status Actions --}}
                        <div class="pt-3 border-t border-gray-100 flex items-center justify-between text-xs">
                            <form method="POST" action="{{ route('owner.beds.update-status', $bed) }}" class="flex items-center gap-1">
                                @csrf
                                @method('PATCH')
                                <select name="status" onchange="this.form.submit()" class="text-xs py-1 px-2 border rounded-md">
                                    <option value="available" {{ $bed->status === 'available' ? 'selected' : '' }}>Available</option>
                                    <option value="occupied" {{ $bed->status === 'occupied' ? 'selected' : '' }} {{ !$tenant ? 'disabled' : '' }}>Occupied</option>
                                    <option value="reserved" {{ $bed->status === 'reserved' ? 'selected' : '' }}>Reserved</option>
                                    <option value="maintenance" {{ $bed->status === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                                    <option value="inactive" {{ $bed->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </form>

                            @if(!$tenant && $bed->status !== 'occupied')
                                <form method="POST" action="{{ route('owner.beds.destroy', $bed) }}" onsubmit="return confirm('Remove Bed {{ $bed->bed_number }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700 font-medium">Remove</button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-gray-400 col-span-2">No beds created yet. Add one using the form on the right.</p>
                @endforelse
            </div>
        </div>

        {{-- Right 1 col: Add Bed Unit Form --}}
        <div>
            <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm space-y-4">
                <h3 class="font-bold text-gray-900 text-sm">Add Bed Unit</h3>
                <p class="text-xs text-gray-500">Manually add an individual bed to Room {{ $room->room_number }}.</p>

                <form method="POST" action="{{ route('owner.rooms.beds.store', $room) }}" class="space-y-3">
                    @csrf

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Bed Number / Code *</label>
                        <input type="text" name="bed_number" required placeholder="e.g. Bed C, Upper 1"
                               class="w-full px-3 py-2 border rounded-lg text-xs focus:ring-2 focus:ring-emerald-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Custom Rental Rate (₱)</label>
                        <input type="number" step="0.01" name="rental_rate" value="{{ $room->rental_rate }}"
                               class="w-full px-3 py-2 border rounded-lg text-xs focus:ring-2 focus:ring-emerald-500">
                        <span class="text-[10px] text-gray-400">Defaults to room rate (₱{{ number_format($room->rental_rate, 2) }})</span>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Initial Status</label>
                        <select name="status" class="w-full px-3 py-2 border rounded-lg text-xs focus:ring-2 focus:ring-emerald-500">
                            <option value="available">Available</option>
                            <option value="reserved">Reserved</option>
                            <option value="maintenance">Maintenance</option>
                        </select>
                    </div>

                    <button type="submit" class="w-full py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg text-xs shadow-sm">
                        + Add Bed
                    </button>
                </form>
            </div>
        </div>

    </div>

</div>
@endsection

