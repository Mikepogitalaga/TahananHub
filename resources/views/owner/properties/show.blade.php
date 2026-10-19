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


    {{-- Rooms & Visual Bed Matrix --}}
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
                <div class="border border-gray-200 rounded-xl bg-gray-50/50 hover:bg-white hover:border-emerald-300 transition flex flex-col justify-between overflow-hidden">
                    <div class="relative w-full aspect-[16/9] bg-gray-100">
                        @if($room->photo)
                            <img src="{{ asset('storage/' . $room->photo) }}" alt="Room {{ $room->room_number }} photo" loading="lazy" decoding="async" onerror="this.hidden=true; this.nextElementSibling.classList.remove('hidden');" class="absolute inset-0 w-full h-full object-cover">
                        @endif
                        <div role="img" aria-label="No room photo available" class="{{ $room->photo ? 'hidden ' : '' }}absolute inset-0 text-gray-500 flex flex-col items-center justify-center gap-2">
                            <svg aria-hidden="true" class="w-9 h-9 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <rect x="3.75" y="4.75" width="16.5" height="14.5" rx="2"></rect>
                                <circle cx="9" cy="10" r="1.5"></circle>
                                <path d="m4.5 17 5-5 3.5 3.5 2.5-2.5 4 4"></path>
                            </svg>
                            <span class="text-xs font-medium">No photo available</span>
                        </div>
                    </div>

                    <div class="p-4 flex flex-col justify-between flex-1">
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
                                <div class="rounded-lg border overflow-hidden text-xs
                                    {{ $bed->status === 'occupied' ? 'bg-emerald-50 border-emerald-200 text-emerald-950' : ($bed->status === 'available' ? 'bg-blue-50 border-blue-200 text-blue-950' : 'bg-gray-100 border-gray-200 text-gray-700') }}">
                                    <div class="p-2 flex items-center gap-2">
                                        <div class="relative w-24 shrink-0 aspect-[16/9] rounded overflow-hidden bg-gray-100">
                                        @if($bed->photo)
                                            <img src="{{ asset('storage/' . $bed->photo) }}" alt="Bed {{ $bed->bed_number }} photo" loading="lazy" decoding="async" onerror="this.hidden=true; this.nextElementSibling.classList.remove('hidden');" class="absolute inset-0 w-full h-full object-cover">
                                        @endif
                                        <div role="img" aria-label="No bed photo available" class="{{ $bed->photo ? 'hidden ' : '' }}absolute inset-0 text-gray-500 flex flex-col items-center justify-center gap-1">
                                            <svg aria-hidden="true" class="w-5 h-5 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                                <rect x="3.75" y="4.75" width="16.5" height="14.5" rx="2"></rect>
                                                <circle cx="9" cy="10" r="1.5"></circle>
                                                <path d="m4.5 17 5-5 3.5 3.5 2.5-2.5 4 4"></path>
                                            </svg>
                                            <span class="text-[9px] font-medium text-center leading-tight">No photo</span>
                                        </div>
                                        </div>
                                        <div class="flex items-center justify-between gap-2 min-w-0 flex-1">
                                            <div class="flex items-center gap-2 min-w-0">
                                            <span class="font-bold font-mono shrink-0">Bed {{ $bed->bed_number }}</span>
                                            @if($tenant)
                                                <span class="text-[11px] text-gray-700 truncate max-w-[120px]">— {{ $tenant->full_name }}</span>
                                            @endif
                                            </div>
                                            <span class="text-[10px] font-bold uppercase px-1.5 py-0.5 rounded shrink-0
                                                {{ $bed->status === 'occupied' ? 'bg-emerald-200 text-emerald-900' : ($bed->status === 'available' ? 'bg-blue-200 text-blue-900' : 'bg-gray-300 text-gray-800') }}">
                                                {{ $bed->status }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <p class="text-[11px] text-gray-400 italic">No beds configured yet.</p>
                            @endforelse
                        </div>
                    </div>

                    <div class="pt-3 mt-4 border-t border-gray-200 flex flex-col-reverse sm:flex-row sm:items-center sm:justify-between gap-2 sm:gap-2">
                        <a href="{{ route('owner.rooms.edit', $room) }}" class="inline-flex items-center justify-center gap-1.5 px-3 py-2 sm:py-1.5 border border-gray-300 rounded-lg text-xs font-semibold text-gray-700 hover:bg-gray-50 hover:border-gray-400 transition w-full sm:w-auto">
                            <svg aria-hidden="true" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <path d="M4 21v-6m8 -6v6M4 10.5V4.5h15.5V10.5"></path>
                            </svg>
                            Edit Room
                        </a>
                        <a href="{{ route('owner.rooms.show', $room) }}" class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 sm:py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg shadow-sm transition w-full sm:w-auto">
                            Manage Beds
                            <svg aria-hidden="true" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <path d="M5 12h14M13 6l6 6-6 6"></path>
                            </svg>
                        </a>
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
