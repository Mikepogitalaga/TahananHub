@extends('layouts.admin')

@section('page_title', 'Protected: Tenants')

@section('content')
<div class="space-y-6">
    @include('admin.protected._banner')

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-bold text-gray-900 text-sm">👥 Tenant Profiles <span class="text-[10px] font-mono text-gray-400 ml-2">VIEW_TENANTS</span></h3>
            @unless($canSeeContact)
                <span class="text-[10px] font-mono text-amber-600">Contact numbers masked (VIEW_TENANTS only)</span>
            @endunless
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs uppercase tracking-wider text-gray-500">
                    <tr>
                        <th class="text-left px-5 py-3">Tenant</th>
                        <th class="text-left px-5 py-3">Room / Bed</th>
                        <th class="text-left px-5 py-3">Contact</th>
                        <th class="text-left px-5 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($tenants as $t)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-3 font-semibold text-gray-900">{{ $t->full_name }}</td>
                            <td class="px-5 py-3 text-xs text-gray-600">
                                @if($t->currentAssignment)
                                    {{ $t->currentAssignment->room?->room_number }} / {{ $t->currentAssignment->bed?->bed_number ?? '—' }}
                                @else
                                    <span class="text-gray-400">Unassigned</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-xs text-gray-600 font-mono">
                                @if($canSeeContact)
                                    {{ $t->contact_number ?? '—' }}
                                @else
                                    •••••••
                                @endif
                            </td>
                            <td class="px-5 py-3"><span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-gray-100 text-gray-700">{{ $t->status }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-5 py-8 text-center text-gray-400 text-xs">No tenants on file.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
