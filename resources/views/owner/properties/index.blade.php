@extends('layouts.owner')

@section('page_title', 'My Properties')

@section('content')
<div class="space-y-6">

    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Boarding House Properties</h2>
            <p class="text-sm text-gray-500">Manage all your boarding facilities, units, and addresses.</p>
        </div>
        <a href="{{ route('owner.properties.create') }}"
           class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-semibold shadow-sm transition">
            + Add New Property
        </a>
    </div>

    {{-- Properties Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse($properties as $property)
            <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm hover:border-emerald-300 transition flex flex-col justify-between">
                <div>
                    <div class="flex items-start justify-between mb-3">
                        <span class="px-2.5 py-1 rounded-md text-xs font-mono font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                            {{ $property->code }}
                        </span>
                        <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold uppercase
                            {{ $property->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-700' }}">
                            {{ str_replace('_', ' ', $property->status) }}
                        </span>
                    </div>

                    <h3 class="font-bold text-gray-900 text-lg mb-1">{{ $property->name }}</h3>
                    <p class="text-xs text-gray-500 mb-3 flex items-center gap-1">
                        <span>📍</span> {{ $property->address }}, {{ $property->barangay }}, {{ $property->municipality }}
                    </p>

                    @if($property->description)
                        <p class="text-xs text-gray-600 line-clamp-2 mb-3">{{ $property->description }}</p>
                    @endif

                    <div class="bg-gray-50 rounded-lg p-2.5 text-xs text-gray-600 mb-3 flex justify-between">
                        <span>Total Rooms: <strong class="text-gray-900">{{ $property->rooms_count }}</strong></span>
                        <span>Contact: <strong>{{ $property->contact_number ?? 'N/A' }}</strong></span>
                    </div>
                </div>

                <div class="pt-3 border-t border-gray-100 flex items-center justify-between">
                    <a href="{{ route('owner.properties.edit', $property) }}" class="text-xs text-gray-500 hover:text-gray-900 font-medium">Edit</a>
                    <a href="{{ route('owner.properties.show', $property) }}" class="px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-xs font-semibold rounded-lg">
                        View Units & Rooms →
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-3 bg-white rounded-xl border border-gray-200 p-12 text-center text-gray-400">
                <span class="text-3xl block mb-2">🏢</span>
                <p class="text-base font-bold text-gray-700">No properties added yet</p>
                <p class="text-xs text-gray-500 mt-1 mb-4">Add your first boarding house property to begin setting up rooms and beds.</p>
                <a href="{{ route('owner.properties.create') }}" class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-xs font-bold shadow-sm">
                    + Add Property
                </a>
            </div>
        @endforelse
    </div>

    @if($properties->hasPages())
        <div>{{ $properties->links() }}</div>
    @endif

</div>
@endsection

