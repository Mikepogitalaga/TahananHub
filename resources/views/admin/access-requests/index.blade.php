@extends('layouts.admin')

@section('page_title', 'Privacy Access Requests')

@section('content')
<div class="space-y-6">

    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Privacy Access Requests</h2>
            <p class="text-sm text-gray-500">Formal temporary access requests submitted to House Owners to inspect private data.</p>
        </div>
        <a href="{{ route('admin.access-requests.create') }}"
           class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-semibold shadow-sm transition">
            + Request Access
        </a>
    </div>

    {{-- Filter Bar --}}
    <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm flex items-center gap-3">
        <a href="{{ route('admin.access-requests.index') }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold {{ !request('status') ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700' }}">All</a>
        <a href="{{ route('admin.access-requests.index', ['status' => 'pending']) }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold {{ request('status') === 'pending' ? 'bg-amber-500 text-white' : 'bg-gray-100 text-gray-700' }}">Pending</a>
        <a href="{{ route('admin.access-requests.index', ['status' => 'approved']) }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold {{ request('status') === 'approved' ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-700' }}">Approved</a>
        <a href="{{ route('admin.access-requests.index', ['status' => 'denied']) }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold {{ request('status') === 'denied' ? 'bg-red-600 text-white' : 'bg-gray-100 text-gray-700' }}">Denied</a>
    </div>

    {{-- Requests Table --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-600">
                <thead class="bg-gray-50 text-xs uppercase font-semibold text-gray-500 border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-3">Owner Business</th>
                        <th class="px-6 py-3">Permissions Requested</th>
                        <th class="px-6 py-3">Duration</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3">Date</th>
                        <th class="px-6 py-3 text-right">Details</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($requests as $req)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-3.5">
                                <p class="font-bold text-gray-900">{{ $req->houseOwner?->business_name ?? 'Owner #' . $req->house_owner_id }}</p>
                                <p class="text-xs text-gray-400">{{ $req->houseOwner?->user?->name }}</p>
                            </td>
                            <td class="px-6 py-3.5">
                                <div class="flex flex-wrap gap-1 max-w-xs">
                                    @foreach(array_slice($req->permissions ?? [], 0, 3) as $perm)
                                        <span class="px-2 py-0.5 bg-gray-100 text-gray-700 rounded text-[10px] font-mono">{{ $perm }}</span>
                                    @endforeach
                                    @if(count($req->permissions ?? []) > 3)
                                        <span class="text-[10px] text-gray-400">+{{ count($req->permissions) - 3 }} more</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-3.5 text-xs text-gray-600">
                                {{ $req->requested_duration_minutes }} minutes
                            </td>
                            <td class="px-6 py-3.5">
                                @if($req->status === 'approved')
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">Approved</span>
                                @elseif($req->status === 'denied')
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-red-50 text-red-700 border border-red-200">Denied</span>
                                @elseif($req->status === 'pending')
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">Pending</span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">{{ ucfirst($req->status) }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-3.5 text-xs text-gray-400">
                                {{ $req->created_at->format('M d, Y h:i A') }}
                            </td>
                            <td class="px-6 py-3.5 text-right">
                                <a href="{{ route('admin.access-requests.show', $req) }}" class="text-xs text-blue-600 hover:underline font-semibold">
                                    View Request →
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-400 text-sm">
                                No access requests on record.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($requests->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $requests->links() }}
            </div>
        @endif
    </div>

</div>
@endsection

