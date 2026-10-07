@extends('layouts.admin')

@section('page_title', 'Protected: Properties')

@section('content')
<div class="space-y-6">
    @include('admin.protected._banner')

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-gray-100">
            <h3 class="font-bold text-gray-900 text-sm">🏢 Property Details <span class="text-[10px] font-mono text-gray-400 ml-2">VIEW_PROPERTY</span></h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs uppercase tracking-wider text-gray-500">
                    <tr>
                        <th class="text-left px-5 py-3">Property</th>
                        <th class="text-left px-5 py-3">Address</th>
                        <th class="text-right px-5 py-3">Rooms</th>
                        <th class="text-right px-5 py-3">Beds</th>
                        <th class="text-left px-5 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($properties as $prop)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-3">
                                <span class="font-semibold text-gray-900">{{ $prop->name }}</span>
                                <span class="block text-[11px] font-mono text-gray-400">{{ $prop->code }}</span>
                            </td>
                            <td class="px-5 py-3 text-xs text-gray-600">{{ $prop->full_address }}</td>
                            <td class="px-5 py-3 text-right">{{ $prop->rooms_count }}</td>
                            <td class="px-5 py-3 text-right">{{ $prop->total_beds }}</td>
                            <td class="px-5 py-3"><span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-gray-100 text-gray-700">{{ $prop->status }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-8 text-center text-gray-400 text-xs">No properties on file.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
