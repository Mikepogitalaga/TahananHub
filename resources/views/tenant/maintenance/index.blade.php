@extends('layouts.tenant')

@section('title', 'Maintenance Requests')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Maintenance & Repair Requests</h1>
            <p class="text-sm text-slate-500">Report plumbing, electrical, WiFi, or room issues directly to your landlord.</p>
        </div>
        <a href="{{ route('tenant.maintenance.create') }}" class="inline-flex items-center space-x-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm rounded-lg shadow transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>Report an Issue</span>
        </a>
    </div>

    <div class="space-y-4">
        @forelse($requests as $req)
            <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm hover:border-slate-300 transition">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-3 border-b border-slate-100">
                    <div class="flex items-center space-x-3">
                        <span class="font-mono text-xs font-bold text-indigo-600">{{ $req->request_number }}</span>
                        <h2 class="text-base font-bold text-slate-800">{{ $req->title }}</h2>
                    </div>
                    <div>
                        @if($req->status === 'resolved')
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase bg-emerald-100 text-emerald-800">RESOLVED</span>
                        @elseif($req->status === 'in_progress')
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase bg-indigo-100 text-indigo-800">IN PROGRESS</span>
                        @elseif($req->status === 'acknowledged')
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase bg-purple-100 text-purple-800">ACKNOWLEDGED</span>
                        @else
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase bg-amber-100 text-amber-800">PENDING</span>
                        @endif
                    </div>
                </div>

                <p class="text-xs text-slate-600 py-3 line-clamp-2 leading-relaxed">{{ $req->description }}</p>

                <div class="flex items-center justify-between pt-3 border-t border-slate-100 text-xs text-slate-400">
                    <div>
                        <span class="uppercase font-semibold text-slate-600">{{ $req->category }}</span> &bull;
                        <span>Priority: <strong class="{{ $req->priority === 'urgent' ? 'text-rose-600' : 'text-slate-700' }} uppercase">{{ $req->priority }}</strong></span> &bull;
                        <span>Reported {{ $req->created_at->diffForHumans() }}</span>
                    </div>
                    <a href="{{ route('tenant.maintenance.show', $req) }}" class="text-indigo-600 hover:underline font-semibold">
                        View Progress & Updates &rarr;
                    </a>
                </div>
            </div>
        @empty
            <div class="bg-white border border-slate-200 rounded-2xl p-12 text-center text-slate-400 shadow-sm">
                <div class="text-3xl mb-2">🔧</div>
                <h3 class="text-base font-semibold text-slate-700">No Maintenance Tickets</h3>
                <p class="text-xs mt-1">If anything needs fixing in your room or common area, file a request here.</p>
                <div class="mt-4">
                    <a href="{{ route('tenant.maintenance.create') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-semibold">
                        Create First Request
                    </a>
                </div>
            </div>
        @endforelse
    </div>

    @if($requests->hasPages())
        <div class="p-4 bg-white border border-slate-200 rounded-xl">
            {{ $requests->links() }}
        </div>
    @endif
</div>
@endsection

