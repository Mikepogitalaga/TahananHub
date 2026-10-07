@extends('layouts.owner')

@section('title', 'Maintenance Requests')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Maintenance & Repair Tickets</h1>
            <p class="text-sm text-slate-500">Track and respond to tenant maintenance reports and facility work orders.</p>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs uppercase font-bold text-slate-400">Pending Review</span>
                <div class="text-2xl font-black text-amber-600 mt-1">{{ $stats['pending'] }}</div>
            </div>
            <div class="w-10 h-10 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center font-bold">
                ⚠️
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs uppercase font-bold text-slate-400">In Progress</span>
                <div class="text-2xl font-black text-indigo-600 mt-1">{{ $stats['in_progress'] }}</div>
            </div>
            <div class="w-10 h-10 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold">
                🔧
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs uppercase font-bold text-slate-400">Resolved</span>
                <div class="text-2xl font-black text-emerald-600 mt-1">{{ $stats['resolved'] }}</div>
            </div>
            <div class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                ✓
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
        <form method="GET" action="{{ route('owner.maintenance.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3">
            <div>
                <select name="property_id" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm bg-white focus:ring-2 focus:ring-indigo-500">
                    <option value="">-- All Properties --</option>
                    @foreach($properties as $prop)
                        <option value="{{ $prop->id }}" {{ request('property_id') == $prop->id ? 'selected' : '' }}>
                            {{ $prop->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <select name="status" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm bg-white focus:ring-2 focus:ring-indigo-500">
                    <option value="">-- All Statuses --</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="acknowledged" {{ request('status') === 'acknowledged' ? 'selected' : '' }}>Acknowledged</option>
                    <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Resolved</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <div>
                <select name="priority" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm bg-white focus:ring-2 focus:ring-indigo-500">
                    <option value="">-- All Priorities --</option>
                    <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Low</option>
                    <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>Medium</option>
                    <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>High</option>
                    <option value="urgent" {{ request('priority') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                </select>
            </div>
            <div class="flex items-center space-x-2">
                <button type="submit" class="w-full px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white font-semibold text-sm rounded-lg transition">Filter</button>
                @if(request()->anyFilled(['property_id', 'status', 'priority']))
                    <a href="{{ route('owner.maintenance.index') }}" class="px-3 py-2 text-slate-500 hover:text-slate-800 text-sm font-semibold">Reset</a>
                @endif
            </div>
        </form>
    </div>

    <!-- Requests Table -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 uppercase text-xs">
                    <tr>
                        <th class="px-5 py-3">Ticket #</th>
                        <th class="px-5 py-3">Tenant & Unit</th>
                        <th class="px-5 py-3">Subject & Category</th>
                        <th class="px-5 py-3">Priority</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Reported</th>
                        <th class="px-5 py-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($requests as $req)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-3.5 font-mono text-xs font-bold text-indigo-600">
                                <a href="{{ route('owner.maintenance.show', $req) }}" class="hover:underline">{{ $req->request_number }}</a>
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="font-semibold text-slate-800">{{ $req->tenant->full_name }}</div>
                                <div class="text-xs text-slate-500">{{ $req->property->name }} &bull; Room {{ $req->room?->room_number ?? '-' }}</div>
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="font-medium text-slate-800">{{ $req->title }}</div>
                                <span class="text-xs text-slate-400 uppercase font-semibold">{{ $req->category }}</span>
                            </td>
                            <td class="px-5 py-3.5">
                                @if($req->priority === 'urgent')
                                    <span class="px-2 py-0.5 rounded text-xs font-bold bg-rose-100 text-rose-800 uppercase animate-pulse">Urgent</span>
                                @elseif($req->priority === 'high')
                                    <span class="px-2 py-0.5 rounded text-xs font-bold bg-amber-100 text-amber-800 uppercase">High</span>
                                @elseif($req->priority === 'medium')
                                    <span class="px-2 py-0.5 rounded text-xs font-semibold bg-blue-100 text-blue-800 uppercase">Medium</span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-xs font-semibold bg-slate-100 text-slate-700 uppercase">Low</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5">
                                @if($req->status === 'resolved')
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">RESOLVED</span>
                                @elseif($req->status === 'in_progress')
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-100 text-indigo-800">IN PROGRESS</span>
                                @elseif($req->status === 'acknowledged')
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-purple-100 text-purple-800">ACKNOWLEDGED</span>
                                @elseif($req->status === 'cancelled')
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-600">CANCELLED</span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800">PENDING</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-xs text-slate-500">
                                {{ $req->created_at->diffForHumans() }}
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <a href="{{ route('owner.maintenance.show', $req) }}" class="px-3 py-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded text-xs font-semibold transition">
                                    Manage Ticket
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-10 text-slate-400">
                                No maintenance requests found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($requests->hasPages())
            <div class="p-4 border-t border-slate-200">
                {{ $requests->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

