@extends('layouts.tenant')

@section('title', 'Ticket ' . $maintenance->request_number)

@section('content')
<div class="space-y-6 max-w-3xl mx-auto">
    <div class="flex items-center justify-between">
        <a href="{{ route('tenant.maintenance.index') }}" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-lg transition flex items-center space-x-1">
            <span>&larr; Back to Tickets</span>
        </a>

        <div>
            @if($maintenance->status === 'resolved')
                <span class="px-3 py-1 rounded-full text-xs font-bold uppercase bg-emerald-100 text-emerald-800">RESOLVED</span>
            @elseif($maintenance->status === 'in_progress')
                <span class="px-3 py-1 rounded-full text-xs font-bold uppercase bg-indigo-100 text-indigo-800">IN PROGRESS</span>
            @elseif($maintenance->status === 'acknowledged')
                <span class="px-3 py-1 rounded-full text-xs font-bold uppercase bg-purple-100 text-purple-800">ACKNOWLEDGED</span>
            @else
                <span class="px-3 py-1 rounded-full text-xs font-bold uppercase bg-amber-100 text-amber-800">PENDING REVIEW</span>
            @endif
        </div>
    </div>

    <!-- Ticket Card -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6 sm:p-8 space-y-6">
        <div class="border-b border-slate-100 pb-4">
            <div class="flex items-center space-x-2">
                <span class="font-mono text-xs font-bold text-indigo-600">{{ $maintenance->request_number }}</span>
                <span class="text-xs uppercase font-semibold text-slate-400">&bull; {{ $maintenance->category }}</span>
            </div>
            <h1 class="text-xl font-bold text-slate-900 mt-1">{{ $maintenance->title }}</h1>
            <p class="text-xs text-slate-500 mt-1">Reported on {{ $maintenance->created_at->format('M d, Y h:i A') }}</p>
        </div>

        <div>
            <span class="text-xs font-bold uppercase text-slate-400 block mb-2">Description</span>
            <div class="p-4 bg-slate-50 rounded-xl border border-slate-100 text-sm text-slate-700 leading-relaxed whitespace-pre-line">
                {{ $maintenance->description }}
            </div>
        </div>

        @if($maintenance->photo_path)
            <div>
                <span class="text-xs font-bold uppercase text-slate-400 block mb-2">Attached Photo</span>
                <a href="{{ asset('storage/' . $maintenance->photo_path) }}" target="_blank" class="inline-block border border-slate-200 rounded-xl overflow-hidden hover:opacity-90 transition">
                    <img src="{{ asset('storage/' . $maintenance->photo_path) }}" alt="Issue Photo" class="max-h-72 object-contain rounded-xl">
                </a>
            </div>
        @endif

        <!-- Landlord Response & Updates Timeline -->
        <div class="pt-6 border-t border-slate-100 space-y-4">
            <h3 class="text-base font-bold text-slate-800">Landlord Status & Updates</h3>

            @if($maintenance->updates->count() > 0)
                <div class="space-y-3">
                    @foreach($maintenance->updates as $update)
                        <div class="p-4 rounded-xl bg-indigo-50/50 border border-indigo-100 space-y-1">
                            <div class="flex justify-between items-center text-xs">
                                <span class="font-bold text-indigo-950">{{ $update->user?->name ?? 'Property Management' }}</span>
                                <span class="text-slate-400">{{ $update->created_at->format('M d, Y h:i A') }}</span>
                            </div>
                            <div class="text-xs font-semibold text-indigo-700 uppercase">Status updated to: {{ $update->status }}</div>
                            <p class="text-xs text-slate-700 leading-relaxed pt-1">{{ $update->note }}</p>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-100 text-xs text-slate-500 text-center">
                    Your request has been received. Your landlord will post updates here regarding repair progress.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

