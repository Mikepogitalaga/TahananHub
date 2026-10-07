@extends('layouts.tenant')

@section('title', 'Announcements')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-extrabold text-slate-900">Announcements</h1>
        <p class="text-sm text-slate-500 mt-1">Notices from your boarding house management and the platform.</p>
    </div>

    <div class="space-y-4">
        @forelse($announcements as $a)
            <article class="bg-white border border-slate-200 rounded-2xl p-5 sm:p-6 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            @if($a->is_platform_wide)
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-blue-50 text-blue-700 border border-blue-200">Platform</span>
                            @elseif($a->property_id)
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-indigo-50 text-indigo-700 border border-indigo-200">My Property</span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-slate-100 text-slate-600 border border-slate-200">Landlord</span>
                            @endif
                        </div>
                        <h2 class="text-lg font-bold text-slate-900">{{ $a->title }}</h2>
                    </div>
                    <span class="text-[11px] text-slate-400 whitespace-nowrap">{{ $a->publish_at?->format('M d, Y') }}</span>
                </div>
                <p class="text-sm text-slate-600 leading-relaxed mt-3 whitespace-pre-line">{{ $a->content }}</p>
                @if($a->expires_at)
                    <p class="text-[11px] text-slate-400 mt-3">Expires: {{ $a->expires_at->format('M d, Y') }}</p>
                @endif
            </article>
        @empty
            <div class="bg-white border border-dashed border-slate-300 rounded-2xl p-10 text-center">
                <span class="text-3xl block mb-2">📢</span>
                <p class="text-sm font-semibold text-slate-600">No announcements yet</p>
                <p class="text-xs text-slate-400 mt-1">New notices from your landlord will appear here.</p>
            </div>
        @endforelse
    </div>

    <div>{{ $announcements->links() }}</div>
</div>
@endsection
