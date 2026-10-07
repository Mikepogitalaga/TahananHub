@extends('layouts.owner')

@section('title', 'Property Announcements')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Boarding House Bulletins & Notices</h1>
            <p class="text-sm text-slate-500">Post announcements visible on tenant dashboards (rules, water interruptions, events).</p>
        </div>
        <a href="{{ route('owner.announcements.create') }}" class="inline-flex items-center space-x-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm rounded-lg shadow transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>New Announcement</span>
        </a>
    </div>

    <!-- Announcements List -->
    <div class="space-y-4">
        @forelse($announcements as $notice)
            <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-sm hover:border-slate-300 transition">
                <div class="flex items-start justify-between">
                    <div>
                        <div class="flex items-center space-x-2">
                            <h2 class="text-lg font-bold text-slate-800">{{ $notice->title }}</h2>
                            @if($notice->status === 'active')
                                <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">ACTIVE</span>
                            @elseif($notice->status === 'draft')
                                <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-600">DRAFT</span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-800">INACTIVE</span>
                            @endif

                            @if($notice->property)
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700">
                                    {{ $notice->property->name }}
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-purple-50 text-purple-700">
                                    All Properties
                                </span>
                            @endif
                        </div>
                        <p class="text-xs text-slate-400 mt-1">Published {{ $notice->publish_at ? \Carbon\Carbon::parse($notice->publish_at)->format('M d, Y h:i A') : 'Immediately' }}</p>
                    </div>

                    <div class="flex items-center space-x-2">
                        <a href="{{ route('owner.announcements.edit', $notice) }}" class="px-3 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded transition">
                            Edit
                        </a>
                        <form method="POST" action="{{ route('owner.announcements.destroy', $notice) }}" onsubmit="return confirm('Delete this announcement?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-3 py-1 bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-semibold rounded transition">
                                Delete
                            </button>
                        </form>
                    </div>
                </div>

                <div class="mt-4 text-sm text-slate-700 whitespace-pre-line leading-relaxed bg-slate-50 p-4 rounded-lg border border-slate-100">
                    {{ $notice->content }}
                </div>
            </div>
        @empty
            <div class="bg-white border border-slate-200 rounded-xl p-12 text-center text-slate-400 shadow-sm">
                <div class="text-3xl mb-2">📢</div>
                <h3 class="text-base font-semibold text-slate-700">No Announcements Posted</h3>
                <p class="text-xs mt-1">Create notices regarding house rules, maintenance advisories, or rent payment reminders.</p>
            </div>
        @endforelse
    </div>

    @if($announcements->hasPages())
        <div class="p-4 bg-white border border-slate-200 rounded-xl">
            {{ $announcements->links() }}
        </div>
    @endif
</div>
@endsection

