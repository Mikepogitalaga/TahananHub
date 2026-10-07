@extends('layouts.owner')

@section('title', 'Ticket ' . $maintenance->request_number)

@section('content')
<div class="space-y-6 max-w-4xl">
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-3">
            <a href="{{ route('owner.maintenance.index') }}" class="p-2 bg-slate-100 hover:bg-slate-200 rounded-lg text-slate-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <div>
                <div class="flex items-center space-x-3">
                    <h1 class="text-2xl font-bold text-slate-800">{{ $maintenance->title }}</h1>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold font-mono bg-slate-100 text-slate-800">{{ $maintenance->request_number }}</span>
                </div>
                <p class="text-xs text-slate-500">Submitted by {{ $maintenance->tenant->full_name }} &bull; {{ $maintenance->created_at->format('M d, Y h:i A') }}</p>
            </div>
        </div>

        <div>
            @if($maintenance->status === 'resolved')
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 uppercase">Resolved</span>
            @elseif($maintenance->status === 'in_progress')
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-indigo-100 text-indigo-800 uppercase">In Progress</span>
            @elseif($maintenance->status === 'acknowledged')
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-purple-100 text-purple-800 uppercase">Acknowledged</span>
            @elseif($maintenance->status === 'cancelled')
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-600 uppercase">Cancelled</span>
            @else
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 uppercase">Pending Review</span>
            @endif
        </div>
    </div>

    <!-- Ticket Summary Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Main Details -->
        <div class="md:col-span-2 space-y-6">
            <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-sm space-y-4">
                <div class="flex justify-between items-center text-xs pb-3 border-b border-slate-100">
                    <span class="font-bold uppercase text-slate-400">Category: <span class="text-slate-800">{{ strtoupper($maintenance->category) }}</span></span>
                    <span class="font-bold uppercase text-slate-400">Priority: <span class="text-rose-600">{{ strtoupper($maintenance->priority) }}</span></span>
                </div>

                <div>
                    <h3 class="text-xs font-bold uppercase text-slate-400 mb-1">Issue Description</h3>
                    <p class="text-sm text-slate-700 whitespace-pre-line leading-relaxed">{{ $maintenance->description }}</p>
                </div>

                @if($maintenance->photo_path)
                    <div class="pt-4 border-t border-slate-100">
                        <h3 class="text-xs font-bold uppercase text-slate-400 mb-2">Attached Photo / Proof</h3>
                        <a href="{{ asset('storage/' . $maintenance->photo_path) }}" target="_blank" class="inline-block border border-slate-200 rounded-lg overflow-hidden hover:opacity-90 transition">
                            <img src="{{ asset('storage/' . $maintenance->photo_path) }}" alt="Issue Photo" class="max-h-64 object-contain rounded-lg">
                        </a>
                    </div>
                @endif
            </div>

            <!-- Resolution / Updates Thread -->
            <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-sm space-y-4">
                <h3 class="text-base font-bold text-slate-800">Activity & Update History</h3>

                <div class="space-y-4">
                    <!-- Initial request -->
                    <div class="flex space-x-3 text-sm">
                        <div class="w-8 h-8 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center font-bold text-xs shrink-0">
                            📝
                        </div>
                        <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 flex-1">
                            <div class="flex justify-between items-center text-xs text-slate-400 mb-1">
                                <span class="font-bold text-slate-700">{{ $maintenance->tenant->full_name }} (Tenant)</span>
                                <span>{{ $maintenance->created_at->format('M d, Y h:i A') }}</span>
                            </div>
                            <p class="text-xs text-slate-600">Created ticket with initial status <span class="font-bold">PENDING</span>.</p>
                        </div>
                    </div>

                    @foreach($maintenance->updates as $update)
                        <div class="flex space-x-3 text-sm">
                            <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-xs shrink-0">
                                💬
                            </div>
                            <div class="bg-indigo-50/50 border border-indigo-100 rounded-xl p-4 flex-1">
                                <div class="flex justify-between items-center text-xs text-slate-400 mb-1">
                                    <span class="font-bold text-indigo-950">{{ $update->user?->name ?? 'Staff' }}</span>
                                    <span>{{ $update->created_at->format('M d, Y h:i A') }}</span>
                                </div>
                                <div class="text-xs text-slate-800 font-medium mb-1">
                                    Status changed to <span class="font-bold uppercase text-indigo-700">{{ $update->status }}</span>
                                </div>
                                <p class="text-xs text-slate-600 leading-relaxed">{{ $update->note }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Post New Update Form -->
            <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-sm">
                <h3 class="text-base font-bold text-slate-800 mb-1">Update Ticket Status & Post Notes</h3>
                <p class="text-xs text-slate-500 mb-4">Post an operational note visible to the tenant and advance the ticket workflow.</p>

                <form method="POST" action="{{ route('owner.maintenance.update-status', $maintenance) }}" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">New Ticket Status *</label>
                        <select name="status" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm bg-white focus:ring-2 focus:ring-indigo-500 font-semibold">
                            <option value="acknowledged" {{ $maintenance->status === 'acknowledged' ? 'selected' : '' }}>Acknowledged (Landlord seen)</option>
                            <option value="in_progress" {{ $maintenance->status === 'in_progress' ? 'selected' : '' }}>In Progress (Technician dispatched / ordered)</option>
                            <option value="resolved" {{ $maintenance->status === 'resolved' ? 'selected' : '' }}>Resolved (Completed)</option>
                            <option value="cancelled" {{ $maintenance->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Notes / Action Details *</label>
                        <textarea name="note" rows="3" required placeholder="Describe action taken or technician schedule..." class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500"></textarea>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm rounded-lg shadow transition">
                            Submit Update
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Sidebar Info -->
        <div class="space-y-6">
            <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm space-y-4 text-sm">
                <h3 class="text-xs font-bold uppercase text-slate-400 tracking-wider">Location & Assignment</h3>

                <div>
                    <span class="block text-xs text-slate-400">Property</span>
                    <span class="font-semibold text-slate-800">{{ $maintenance->property->name }}</span>
                    <p class="text-xs text-slate-500">{{ $maintenance->property->address }}</p>
                </div>

                <div>
                    <span class="block text-xs text-slate-400">Room & Bed</span>
                    <span class="font-semibold text-slate-800">
                        Room {{ $maintenance->room?->room_number ?? 'N/A' }}
                        @if($maintenance->bed)
                            &bull; Bed {{ $maintenance->bed->bed_number }}
                        @endif
                    </span>
                </div>

                <div class="pt-3 border-t border-slate-100">
                    <span class="block text-xs text-slate-400">Tenant Contact</span>
                    <span class="font-semibold text-slate-800 block">{{ $maintenance->tenant->full_name }}</span>
                    <span class="text-xs text-slate-600 block">📞 {{ $maintenance->tenant->contact_number ?? 'No phone' }}</span>
                    <span class="text-xs text-slate-600 block">✉️ {{ $maintenance->tenant->email ?? 'No email' }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

