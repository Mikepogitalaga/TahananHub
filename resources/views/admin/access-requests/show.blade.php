@extends('layouts.admin')

@section('page_title', 'Access Request #' . $accessRequest->id)

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <a href="{{ route('admin.access-requests.index') }}" class="text-xs text-blue-600 hover:underline">← Back to Requests</a>
        <span class="px-3 py-1 rounded-full text-xs font-bold uppercase
            {{ $accessRequest->status === 'approved' ? 'bg-emerald-100 text-emerald-800' : ($accessRequest->status === 'denied' ? 'bg-red-100 text-red-800' : 'bg-amber-100 text-amber-800') }}">
            Status: {{ $accessRequest->status }}
        </span>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm space-y-5">
        <div>
            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Target Workspace</h3>
            <p class="text-lg font-bold text-gray-900">
                {{ $accessRequest->houseOwner?->business_name ?? 'Owner #' . $accessRequest->house_owner_id }}
            </p>
            <p class="text-xs text-gray-500">
                Owner User: {{ $accessRequest->houseOwner?->user?->name }} ({{ $accessRequest->houseOwner?->user?->email }})
            </p>
        </div>

        <div class="border-t border-gray-100 pt-4">
            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Requested Permissions</h3>
            <div class="flex flex-wrap gap-1.5">
                @foreach($accessRequest->permissions ?? [] as $perm)
                    <span class="px-2.5 py-1 bg-blue-50 text-blue-700 rounded-md text-xs font-mono font-semibold">
                        {{ $perm }}
                    </span>
                @endforeach
            </div>
        </div>

        <div class="border-t border-gray-100 pt-4 grid grid-cols-2 gap-4 text-xs">
            <div>
                <span class="text-gray-400 block mb-0.5">Duration Requested</span>
                <span class="font-bold text-gray-800 text-sm">{{ $accessRequest->requested_duration_minutes }} Minutes</span>
            </div>
            <div>
                <span class="text-gray-400 block mb-0.5">Submitted At</span>
                <span class="text-gray-700">{{ $accessRequest->created_at->format('M d, Y h:i A') }}</span>
            </div>
        </div>

        <div class="border-t border-gray-100 pt-4">
            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Reason Provided</h3>
            <div class="p-3 bg-gray-50 rounded-lg text-sm text-gray-700 italic border border-gray-200">
                "{{ $accessRequest->reason }}"
            </div>
        </div>

        @if($accessRequest->owner_notes)
            <div class="border-t border-gray-100 pt-4">
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Owner Response Notes</h3>
                <div class="p-3 bg-gray-50 rounded-lg text-sm text-gray-700 border border-gray-200">
                    {{ $accessRequest->owner_notes }}
                </div>
            </div>
        @endif

        @if($accessRequest->status === 'approved')
            <div class="border-t border-gray-100 pt-4 flex items-center justify-between">
                <div>
                    <span class="text-emerald-700 font-semibold text-xs block">✓ Approved by Owner</span>
                    <span class="text-[11px] text-gray-400">Temporary session has been generated.</span>
                </div>
                <a href="{{ route('admin.owners.show', $accessRequest->house_owner_id) }}"
                   class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold">
                    Go to Workspace →
                </a>
            </div>
        @endif
    </div>

</div>
@endsection

