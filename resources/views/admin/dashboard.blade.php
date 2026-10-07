@extends('layouts.admin')

@section('page_title', 'Platform Overview')

@section('content')
<div class="space-y-6">

    {{-- Privacy Banner Notice --}}
    <div class="bg-gradient-to-r from-blue-900 to-indigo-900 rounded-2xl p-6 text-white shadow-md relative overflow-hidden">
        <div class="relative z-10 max-w-2xl">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-blue-500/30 text-blue-200 border border-blue-400/30 mb-3">
                🛡️ Architectural Privacy Enforced
            </span>
            <h2 class="text-2xl font-bold tracking-tight">Platform Administration Console</h2>
            <p class="text-blue-200 text-sm mt-2 leading-relaxed">
                Super Admins manage platform accounts, settings, and health. In accordance with zero-trust design, House Owner tenant records, financial revenues, and private leases are isolated and protected by default.
            </p>
        </div>
    </div>

    {{-- Stats KPI Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
            <div class="flex items-center justify-between text-gray-500 text-sm mb-2">
                <span>Total Accounts</span>
                <span class="text-lg">👥</span>
            </div>
            <p class="text-3xl font-extrabold text-gray-900">{{ $totalUsers }}</p>
            <p class="text-xs text-gray-500 mt-1">Across all 3 user roles</p>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
            <div class="flex items-center justify-between text-gray-500 text-sm mb-2">
                <span>House Owners</span>
                <span class="text-lg">🏢</span>
            </div>
            <p class="text-3xl font-extrabold text-blue-600">{{ $totalOwners }}</p>
            <p class="text-xs text-emerald-600 font-medium mt-1">{{ $activeOwners }} active businesses</p>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
            <div class="flex items-center justify-between text-gray-500 text-sm mb-2">
                <span>Total Properties</span>
                <span class="text-lg">🏘️</span>
            </div>
            <p class="text-3xl font-extrabold text-indigo-600">{{ $totalProperties }}</p>
            <p class="text-xs text-gray-500 mt-1">Registered boarding facilities</p>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
            <div class="flex items-center justify-between text-gray-500 text-sm mb-2">
                <span>Active Privacy Sessions</span>
                <span class="text-lg">🔐</span>
            </div>
            <p class="text-3xl font-extrabold {{ $activeSessions->count() > 0 ? 'text-amber-600' : 'text-gray-900' }}">
                {{ $activeSessions->count() }}
            </p>
            <p class="text-xs text-gray-500 mt-1">Approved temporary access</p>
        </div>
    </div>

    {{-- Two Column Content: Active Privacy Sessions & Recent Access Requests --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Active Privacy Sessions Card --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm flex flex-col">
            <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                <div class="flex items-center gap-2">
                    <span class="text-lg">🔓</span>
                    <h3 class="font-bold text-gray-900 text-base">Active Temporary Access Sessions</h3>
                </div>
                <a href="{{ route('admin.access-requests.create') }}" class="text-xs text-blue-600 font-semibold hover:underline">
                    + New Request
                </a>
            </div>

            <div class="flex-1 divide-y divide-gray-100">
                @forelse($activeSessions as $session)
                    <div class="py-3 flex items-center justify-between">
                        <div>
                            <p class="font-semibold text-sm text-gray-900">
                                {{ $session->houseOwner?->business_name ?? $session->houseOwner?->user?->name }}
                            </p>
                            <p class="text-xs text-gray-500 mt-0.5">
                                Expires in: <span class="text-amber-600 font-medium font-mono">{{ $session->expires_at->diffForHumans() }}</span>
                            </p>
                            <div class="flex flex-wrap gap-1 mt-1.5">
                                @foreach($session->permissions as $perm)
                                    <span class="px-2 py-0.5 bg-blue-50 text-blue-700 rounded text-[10px] font-mono font-medium">
                                        {{ $perm }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                        <a href="{{ route('admin.owners.show', $session->house_owner_id) }}" 
                           class="px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-semibold rounded-lg">
                            Inspect Data →
                        </a>
                    </div>
                @empty
                    <div class="py-8 text-center text-gray-400 text-sm">
                        <span class="text-2xl block mb-1">🔒</span>
                        No active access sessions. All House Owner business data is strictly private.
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Recent Access Requests Card --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm flex flex-col">
            <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                <div class="flex items-center gap-2">
                    <span class="text-lg">📋</span>
                    <h3 class="font-bold text-gray-900 text-base">Recent Access Requests</h3>
                </div>
                <a href="{{ route('admin.access-requests.index') }}" class="text-xs text-blue-600 font-semibold hover:underline">
                    View All
                </a>
            </div>

            <div class="flex-1 divide-y divide-gray-100">
                @forelse($recentRequests as $req)
                    <div class="py-3 flex items-center justify-between">
                        <div>
                            <p class="font-semibold text-sm text-gray-900">
                                To: {{ $req->houseOwner?->business_name ?? 'Owner #' . $req->house_owner_id }}
                            </p>
                            <p class="text-xs text-gray-500 truncate max-w-xs mt-0.5">
                                Reason: {{ $req->reason }}
                            </p>
                            <p class="text-[10px] text-gray-400 mt-1 font-mono">
                                Requested {{ $req->created_at->diffForHumans() }}
                            </p>
                        </div>
                        <div>
                            @if($req->status === 'approved')
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">Approved</span>
                            @elseif($req->status === 'denied')
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-red-50 text-red-700 border border-red-200">Denied</span>
                            @elseif($req->status === 'pending')
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">Pending</span>
                            @else
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">{{ ucfirst($req->status) }}</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="py-8 text-center text-gray-400 text-sm">
                        No access requests on file.
                    </div>
                @endforelse
            </div>
        </div>

    </div>

    {{-- Recent Users Table --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
        <div class="p-5 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-bold text-gray-900 text-base">Recently Registered Accounts</h3>
            <a href="{{ route('admin.users.index') }}" class="text-xs text-blue-600 font-semibold hover:underline">
                View All Accounts
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-600">
                <thead class="bg-gray-50 text-xs uppercase font-semibold text-gray-500 border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-3">Name & Email</th>
                        <th class="px-6 py-3">Role</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3">Joined Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($recentUsers as $user)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-3.5">
                                <p class="font-medium text-gray-900">{{ $user->name }}</p>
                                <p class="text-xs text-gray-400">{{ $user->email }}</p>
                            </td>
                            <td class="px-6 py-3.5">
                                <span class="px-2.5 py-1 rounded-md text-xs font-semibold uppercase
                                    {{ $user->role === 'super_admin' ? 'bg-purple-100 text-purple-800' : ($user->role === 'owner' ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800') }}">
                                    {{ str_replace('_', ' ', $user->role) }}
                                </span>
                            </td>
                            <td class="px-6 py-3.5">
                                <span class="inline-flex items-center gap-1.5 text-xs font-semibold {{ $user->status === 'active' ? 'text-emerald-700' : 'text-red-700' }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $user->status === 'active' ? 'bg-emerald-500' : 'bg-red-500' }}"></span>
                                    {{ ucfirst($user->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-3.5 text-xs text-gray-400">
                                {{ $user->created_at->format('M d, Y') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

