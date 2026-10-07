@extends('layouts.owner')

@section('page_title', $property->name)

@section('content')
<div class="space-y-6">

    {{-- Top Navigation & Actions --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <a href="{{ route('owner.properties.index') }}" class="text-xs text-emerald-600 hover:underline mb-1 inline-block">← Back to Properties</a>
            <div class="flex items-center gap-3">
                <h2 class="text-2xl font-bold text-gray-900">{{ $property->name }}</h2>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold uppercase
                    {{ $property->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-700' }}">
                    {{ str_replace('_', ' ', $property->status) }}
                </span>
            </div>
            <p class="text-xs text-gray-500 mt-1">
                📍 {{ $property->address }}, {{ $property->barangay }}, {{ $property->municipality }}, {{ $property->province }}
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('owner.properties.edit', $property) }}" class="px-3.5 py-2 border border-gray-300 rounded-lg text-xs font-semibold text-gray-700 hover:bg-gray-50">
                Edit Property
            </a>
            <a href="{{ route('owner.rooms.create', ['property_id' => $property->id]) }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold shadow-sm">
                + Add Room
            </a>
        </div>
    </div>

    {{-- Quick KPI Stats --}}
    @php
        $totalRooms = $property->rooms->count();
        $allBeds = $property->rooms->flatMap->beds;
        $totalBeds = $allBeds->count();
        $occupiedBeds = $allBeds->where('status', 'occupied')->count();
        $availableBeds = $allBeds->where('status', 'available')->count();
    @endphp

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
            <span class="text-xs text-gray-500 block">Total Rooms</span>
            <span class="text-2xl font-bold text-gray-900">{{ $totalRooms }}</span>
        </div>
        <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
            <span class="text-xs text-gray-500 block">Total Beds</span>
            <span class="text-2xl font-bold text-gray-900">{{ $totalBeds }}</span>
        </div>
        <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
            <span class="text-xs text-emerald-600 block">Occupied Beds</span>
            <span class="text-2xl font-bold text-emerald-600">{{ $occupiedBeds }}</span>
        </div>
        <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
            <span class="text-xs text-blue-600 block">Available Beds</span>
            <span class="text-2xl font-bold text-blue-600">{{ $availableBeds }}</span>
        </div>
    </div>

    {{-- Rooms & Visual Bed Matrix (Requirement #19) --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm">
        <div class="flex items-center justify-between pb-4 border-b border-gray-100 mb-6">
            <div>
                <h3 class="font-bold text-gray-900 text-base">Rooms & Visual Bed Occupancy</h3>
                <p class="text-xs text-gray-500">Real-time status of each room and its individual bed units.</p>
            </div>
            <a href="{{ route('owner.rooms.create', ['property_id' => $property->id]) }}" class="text-xs text-emerald-600 font-semibold hover:underline">
                + Add Room to this Property
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @forelse($property->rooms as $room)
                <div class="border border-gray-200 rounded-xl p-4 bg-gray-50/50 hover:bg-white hover:border-emerald-300 transition flex flex-col justify-between">
                    <div>
                        <div class="flex items-start justify-between mb-2">
                            <div>
                                <h4 class="font-bold text-gray-900 text-base">Room {{ $room->room_number }}</h4>
                                <span class="text-xs text-gray-500">{{ $room->room_name ?? ucfirst($room->room_type) }}</span>
                            </div>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase
                                {{ $room->status === 'available' ? 'bg-emerald-100 text-emerald-800' : ($room->status === 'full' ? 'bg-amber-100 text-amber-800' : 'bg-gray-200 text-gray-700') }}">
                                {{ $room->status }}
                            </span>
                        </div>

                        <div class="text-xs text-gray-600 mb-3">
                            <span>Rate: <strong>₱{{ number_format($room->rental_rate, 2) }}/mo</strong></span>
                            @if($room->floor) <span class="ml-2">Floor: {{ $room->floor }}</span> @endif
                        </div>

                        {{-- Beds in this Room --}}
                        <div class="space-y-1.5 pt-2 border-t border-gray-200">
                            <span class="text-[10px] uppercase font-bold text-gray-400 block mb-1">Beds ({{ $room->beds->count() }}/{{ $room->capacity }})</span>
                            @forelse($room->beds as $bed)
                                @php
                                    $tenant = $bed->currentAssignment?->tenant;
                                @endphp
                                <div class="p-2 rounded-lg border text-xs flex items-center justify-between
                                    {{ $bed->status === 'occupied' ? 'bg-emerald-50 border-emerald-200 text-emerald-950' : ($bed->status === 'available' ? 'bg-blue-50 border-blue-200 text-blue-950' : 'bg-gray-100 border-gray-200 text-gray-700') }}">
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold font-mono">Bed {{ $bed->bed_number }}</span>
                                        @if($tenant)
                                            <span class="text-[11px] text-gray-700 truncate max-w-[120px]">— {{ $tenant->full_name }}</span>
                                        @endif
                                    </div>
                                    <span class="text-[10px] font-bold uppercase px-1.5 py-0.5 rounded
                                        {{ $bed->status === 'occupied' ? 'bg-emerald-200 text-emerald-900' : ($bed->status === 'available' ? 'bg-blue-200 text-blue-900' : 'bg-gray-300 text-gray-800') }}">
                                        {{ $bed->status }}
                                    </span>
                                </div>
                            @empty
                                <p class="text-[11px] text-gray-400 italic">No beds configured yet.</p>
                            @endforelse
                        </div>
                    </div>

                    <div class="pt-3 mt-4 border-t border-gray-200 flex items-center justify-between text-xs">
                        <a href="{{ route('owner.rooms.edit', $room) }}" class="text-gray-500 hover:text-gray-900">Edit Room</a>
                        <a href="{{ route('owner.rooms.show', $room) }}" class="text-emerald-600 font-semibold hover:underline">Manage Beds →</a>
                    </div>
                </div>
            @empty
                <div class="col-span-3 text-center py-8 text-gray-400 text-sm">
                    No rooms added to this property yet.
                </div>
            @endforelse
        </div>
    </div>

</div>
@endsection

