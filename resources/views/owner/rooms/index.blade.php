@extends('layouts.owner')

@section('page_title', 'Rooms & Beds')

@section('content')
<div class="space-y-6">

    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Rooms & Bed Units</h2>
            <p class="text-sm text-gray-500">Monitor room allocations, capacities, and bed occupancy.</p>
        </div>
        <a href="{{ route('owner.rooms.create') }}"
           class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-semibold shadow-sm transition">
            + Add New Room
        </a>
    </div>

    {{-- Filter by Property --}}
    <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
        <form method="GET" action="{{ route('owner.rooms.index') }}" class="flex flex-wrap items-center gap-3">
            <select name="property_id" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500">
                <option value="">All Properties</option>
                @foreach($properties as $prop)
                    <option value="{{ $prop->id }}" {{ request('property_id') == $prop->id ? 'selected' : '' }}>
                        {{ $prop->name }} ({{ $prop->code }})
                    </option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-lg text-sm font-medium">Filter</button>
            <a href="{{ route('owner.rooms.index') }}" class="px-3 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium">Reset</a>
        </form>
    </div>

    {{-- Rooms Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse($rooms as $room)
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm hover:border-emerald-300 transition flex flex-col justify-between overflow-hidden">
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

                <div class="p-5 flex flex-col justify-between flex-1">
                    <div class="flex items-start justify-between mb-2">
                        <div>
                            <span class="text-[10px] text-gray-400 block font-mono">{{ $room->property?->name }}</span>
                            <h3 class="font-bold text-gray-900 text-lg">Room {{ $room->room_number }}</h3>
                        </div>
                        <span class="px-2 py-0.5 rounded text-xs font-bold uppercase
                            {{ $room->status === 'available' ? 'bg-emerald-100 text-emerald-800' : ($room->status === 'full' ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-700') }}">
                            {{ $room->status }}
                        </span>
                    </div>

                    <div class="text-xs text-gray-600 mb-3 space-y-1">
                        <p>Type: <strong class="uppercase">{{ $room->room_type }}</strong> (Cap: {{ $room->capacity }})</p>
                        <p>Rate: <strong class="text-emerald-700 font-bold">₱{{ number_format($room->rental_rate, 2) }}/mo</strong></p>
                    </div>

                    {{-- Visual Beds in this Room --}}
                    <div class="bg-gray-50 rounded-lg p-2.5 space-y-1.5 mb-3 border border-gray-100">
                        <div class="flex justify-between text-[11px] font-semibold text-gray-500 mb-1">
                            <span>Beds Status</span>
                            <span>{{ $room->beds->where('status', 'occupied')->count() }}/{{ $room->beds->count() }} Occupied</span>
                        </div>
                        <div class="grid grid-cols-2 gap-1.5">
                            @foreach($room->beds as $bed)
                                <div class="px-2 py-1 rounded text-[11px] flex items-center justify-between font-mono
                                    {{ $bed->status === 'occupied' ? 'bg-emerald-100 text-emerald-900' : ($bed->status === 'available' ? 'bg-blue-100 text-blue-900' : 'bg-gray-200 text-gray-700') }}">
                                    <span>Bed {{ $bed->bed_number }}</span>
                                    <span class="text-[9px] font-bold uppercase">{{ $bed->status }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="pt-3 border-t border-gray-100 flex flex-col-reverse sm:flex-row sm:items-center sm:justify-between gap-2 sm:gap-2">
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
            <div class="col-span-3 bg-white rounded-xl border border-gray-200 p-8 text-center text-gray-400">
                No rooms found. Add a room to get started.
            </div>
        @endforelse
    </div>

    @if($rooms->hasPages())
        <div>{{ $rooms->links() }}</div>
    @endif

</div>
@endsection
