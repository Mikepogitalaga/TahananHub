@extends('layouts.admin')

@section('page_title', 'Protected: Rooms')

@section('content')
<div class="space-y-6">
    @include('admin.protected._banner')

    @forelse($rooms as $prop)
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-gray-100">
                <h3 class="font-bold text-gray-900 text-sm">🏢 {{ $prop->name }} <span class="text-[10px] font-mono text-gray-400 ml-2">VIEW_ROOMS</span></h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs uppercase tracking-wider text-gray-500">
                        <tr>
                            <th class="text-left px-5 py-3">Room</th>
                            <th class="text-left px-5 py-3">Type</th>
                            <th class="text-left px-5 py-3">Floor</th>
                            <th class="text-right px-5 py-3">Capacity</th>
                            <th class="text-right px-5 py-3">Rate</th>
                            <th class="text-left px-5 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($prop->rooms as $room)
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-3 font-semibold text-gray-900">{{ $room->room_number }} {{ $room->room_name ? '(' . $room->room_name . ')' : '' }}</td>
                                <td class="px-5 py-3 text-xs text-gray-600">{{ $room->room_type }}</td>
                                <td class="px-5 py-3 text-xs text-gray-600">{{ $room->floor ?? '—' }}</td>
                                <td class="px-5 py-3 text-right">{{ $room->capacity }}</td>
                                <td class="px-5 py-3 text-right">₱{{ number_format($room->rental_rate, 2) }}</td>
                                <td class="px-5 py-3"><span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-gray-100 text-gray-700">{{ $room->status }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-5 py-6 text-center text-gray-400 text-xs">No rooms on file.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @empty
        <div class="bg-white rounded-xl border border-gray-200 p-8 text-center text-gray-400 text-xs">No properties on file.</div>
    @endforelse
</div>
@endsection
