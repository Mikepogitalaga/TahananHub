@extends('layouts.owner')

@section('page_title', 'Tenants')

@section('content')
<div class="space-y-6">

    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Tenant Directory</h2>
            <p class="text-sm text-gray-500">Manage tenant profiles, contact details, and bed assignments.</p>
        </div>
        <a href="{{ route('owner.tenants.create') }}"
           class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-semibold shadow-sm transition">
            + Register Tenant
        </a>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
        <form method="GET" action="{{ route('owner.tenants.index') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, phone, email..."
                   class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500">

            <select name="status" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500">
                <option value="">All Statuses</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="moved_out" {{ request('status') === 'moved_out' ? 'selected' : '' }}>Moved Out</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
            </select>

            <div class="flex gap-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-gray-800 text-white rounded-lg text-sm font-medium">Filter</button>
                <a href="{{ route('owner.tenants.index') }}" class="px-3 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium">Reset</a>
            </div>
        </form>
    </div>

    {{-- Tenants Table --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-600">
                <thead class="bg-gray-50 text-xs uppercase font-semibold text-gray-500 border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-3">Tenant Name</th>
                        <th class="px-6 py-3">Assigned Unit</th>
                        <th class="px-6 py-3">Contact</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($tenants as $tenant)
                        @php $assignment = $tenant->currentAssignment; @endphp
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-emerald-100 text-emerald-800 font-bold flex items-center justify-center text-xs">
                                        {{ substr($tenant->first_name, 0, 1) }}{{ substr($tenant->last_name, 0, 1) }}
                                    </div>
                                    <div>
                                        <a href="{{ route('owner.tenants.show', $tenant) }}" class="font-bold text-gray-900 hover:text-emerald-600">
                                            {{ $tenant->full_name }}
                                        </a>
                                        <p class="text-xs text-gray-400">{{ $tenant->email ?? 'No email' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-3.5">
                                @if($assignment)
                                    <span class="font-medium text-xs text-gray-800 block">
                                        {{ $assignment->property?->name }}
                                    </span>
                                    <span class="text-[11px] text-gray-500 font-mono">
                                        Room {{ $assignment->room?->room_number }} – Bed {{ $assignment->bed?->bed_number }}
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400 italic">Not Assigned</span>
                                @endif
                            </td>
                            <td class="px-6 py-3.5 text-xs text-gray-600">
                                {{ $tenant->contact_number ?? '—' }}
                            </td>
                            <td class="px-6 py-3.5">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold
                                    {{ $tenant->status === 'active' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : ($tenant->status === 'moved_out' ? 'bg-gray-100 text-gray-700' : 'bg-red-50 text-red-700') }}">
                                    {{ ucfirst(str_replace('_', ' ', $tenant->status)) }}
                                </span>
                            </td>
                            <td class="px-6 py-3.5 text-right space-x-2">
                                <a href="{{ route('owner.tenants.show', $tenant) }}" class="text-xs text-emerald-600 font-semibold hover:underline">Profile</a>
                                <a href="{{ route('owner.tenants.edit', $tenant) }}" class="text-xs text-gray-500 font-semibold hover:underline">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-400 text-sm">
                                No tenants found. Register your first tenant above.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($tenants->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">{{ $tenants->links() }}</div>
        @endif
    </div>

</div>
@endsection

