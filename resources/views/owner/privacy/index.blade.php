@extends('layouts.owner')

@section('title', 'Privacy & Admin Access Control')

@section('content')
<div class="space-y-6 max-w-5xl">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Data Privacy & Admin Access Control</h1>
            <p class="text-sm text-slate-500">Manage platform administration boundaries. Your private business data is protected by default.</p>
        </div>
        <div class="inline-flex items-center space-x-2 px-3 py-1.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 1.944A11.954 11.954 0 012.166 5C2.056 5.649 2 6.319 2 7c0 5.225 3.34 9.67 8 11.317C14.66 16.67 18 12.225 18 7c0-.682-.057-1.35-.166-2.001A11.954 11.954 0 0110 1.944zM11 14a1 1 0 11-2 0 1 1 0 012 0zm0-7a1 1 0 10-2 0v3a1 1 0 102 0V7z" clip-rule="evenodd"/></svg>
            <span>Strict Privacy Boundary Active</span>
        </div>
    </div>

    <!-- Privacy Architecture Guarantee Banner -->
    <div class="bg-gradient-to-r from-slate-900 to-indigo-950 text-white rounded-2xl p-6 shadow-md border border-slate-800">
        <div class="flex items-start space-x-4">
            <div class="w-12 h-12 rounded-xl bg-indigo-500/20 text-indigo-400 flex items-center justify-center font-bold text-xl shrink-0 border border-indigo-400/30">
                🛡️
            </div>
            <div>
                <h2 class="text-lg font-bold text-white">Private Business Guarantee</h2>
                <p class="text-xs text-slate-300 mt-1 leading-relaxed">
                    By design, Platform Super Administrators cannot view your tenant personal information, IDs, revenue, or lease contracts without your explicit, time-limited consent. When an admin requests technical or support access, you hold absolute authority to grant, customize, or immediately revoke their session.
                </p>
            </div>
        </div>
    </div>

    <!-- Active Sessions Section -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
        <h2 class="text-lg font-bold text-slate-800 mb-1">Active Administrator Sessions</h2>
        <p class="text-xs text-slate-500 mb-4">Administrators who currently hold temporary authorized access to your records.</p>

        @if($activeSessions->count() > 0)
            <div class="space-y-4">
                @foreach($activeSessions as $session)
                    <div class="p-5 rounded-xl border-2 border-emerald-300 bg-emerald-50/40 flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div>
                            <div class="flex items-center space-x-3">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase bg-emerald-600 text-white animate-pulse">Live Session</span>
                                <span class="font-bold text-slate-900">{{ $session->admin->name }}</span>
                                <span class="text-xs text-slate-500">({{ $session->admin->email }})</span>
                            </div>

                            <div class="mt-2 text-xs text-slate-600 space-y-1">
                                <div><span class="font-semibold">Granted:</span> {{ $session->granted_at->format('M d, Y h:i A') }}</div>
                                <div><span class="font-semibold text-rose-700">Expires:</span> {{ $session->expires_at->format('M d, Y h:i A') }} ({{ $session->expires_at->diffForHumans() }})</div>
                            </div>

                            <div class="mt-3">
                                <span class="text-xs uppercase font-bold text-slate-500 block mb-1">Authorized Permissions:</span>
                                <div class="flex flex-wrap gap-1">
                                    @foreach($session->permissions as $perm)
                                        <span class="px-2 py-0.5 rounded text-[11px] font-medium bg-white text-slate-700 border border-slate-200">
                                            {{ $permissionsMap[$perm] ?? $perm }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div>
                            <form method="POST" action="{{ route('owner.privacy.revoke', $session) }}" onsubmit="return confirm('Immediately terminate this admin session? The admin will lose access immediately.');">
                                @csrf
                                <button type="submit" class="w-full md:w-auto px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs uppercase rounded-lg shadow transition">
                                    Revoke Access Now
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-6 text-sm text-slate-400 bg-slate-50 rounded-xl border border-slate-100">
                🔒 No active administrator access sessions. Your business data is completely sealed.
            </div>
        @endif
    </div>

    <!-- Pending Access Requests Section -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
        <h2 class="text-lg font-bold text-slate-800 mb-1">Pending Access Requests</h2>
        <p class="text-xs text-slate-500 mb-4">Requests from platform administrators asking for temporary data access.</p>

        @if($pendingRequests->count() > 0)
            <div class="space-y-5">
                @foreach($pendingRequests as $req)
                    <div class="p-5 rounded-xl border-2 border-amber-300 bg-amber-50/50 space-y-4">
                        <div class="flex justify-between items-start">
                            <div>
                                <div class="flex items-center space-x-2">
                                    <span class="px-2.5 py-0.5 rounded text-xs font-bold uppercase bg-amber-200 text-amber-900">Needs Approval</span>
                                    <h3 class="font-bold text-slate-900 text-base">{{ $req->requestedBy->name }} (Platform Admin)</h3>
                                </div>
                                <p class="text-xs text-slate-500 mt-1">Requested {{ $req->created_at->diffForHumans() }} &bull; Duration requested: <span class="font-bold text-slate-800">{{ $req->requested_duration_minutes }} Minutes</span></p>
                            </div>
                        </div>

                        <!-- Reason -->
                        <div class="bg-white p-3 rounded-lg border border-amber-200 text-sm">
                            <span class="text-xs font-bold uppercase text-slate-400 block mb-1">Stated Reason for Access:</span>
                            <p class="text-slate-800 font-medium">{{ $req->reason }}</p>
                            @if($req->request_notes)
                                <p class="text-xs text-slate-500 mt-1 italic">{{ $req->request_notes }}</p>
                            @endif
                        </div>

                        <!-- Permissions Requested -->
                        <div>
                            <span class="text-xs font-bold uppercase text-slate-500 block mb-1.5">Permissions Requested:</span>
                            <div class="flex flex-wrap gap-1.5">
                                @foreach($req->permissions as $perm)
                                    <span class="px-2.5 py-1 rounded-md text-xs font-semibold bg-white text-indigo-900 border border-indigo-200">
                                        ✓ {{ $permissionsMap[$perm] ?? $perm }}
                                    </span>
                                @endforeach
                            </div>
                        </div>

                        <!-- Actions Form -->
                        <form method="POST" action="{{ route('owner.privacy.respond', $req) }}" class="pt-2 border-t border-amber-200/60 flex flex-wrap items-center justify-between gap-3">
                            @csrf
                            <input type="text" name="notes" placeholder="Optional notes to the admin..." class="px-3 py-1.5 border border-slate-300 rounded-lg text-xs bg-white flex-1 min-w-[200px]">

                            <div class="flex items-center space-x-2">
                                <button type="submit" name="action" value="deny" onclick="return confirm('Deny this access request?');" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 font-semibold text-xs rounded-lg transition">
                                    Deny Request
                                </button>
                                <button type="submit" name="action" value="approve" onclick="return confirm('Grant temporary access to the administrator for {{ $req->requested_duration_minutes }} minutes?');" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs uppercase rounded-lg shadow transition">
                                    Approve Temporary Access
                                </button>
                            </div>
                        </form>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-6 text-sm text-slate-400 bg-slate-50 rounded-xl border border-slate-100">
                No pending administrator access requests.
            </div>
        @endif
    </div>

    <!-- Privacy Preferences Setting -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
        <h2 class="text-lg font-bold text-slate-800 mb-1">Privacy Preferences</h2>
        <p class="text-xs text-slate-500 mb-4">Configure security behavior for your business workspace.</p>

        <form method="POST" action="{{ route('owner.privacy.settings') }}" class="space-y-4">
            @csrf

            <label class="flex items-start space-x-3 cursor-pointer">
                <input type="checkbox" name="privacy_require_approval" value="1" {{ $owner->privacy_require_approval ? 'checked' : '' }} class="mt-0.5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                <div>
                    <span class="text-sm font-semibold text-slate-800 block">Require Explicit Owner Approval (Recommended)</span>
                    <span class="text-xs text-slate-500">Platform administrators must request permission and await your confirmation before inspecting any private data.</span>
                </div>
            </label>

            <label class="flex items-start space-x-3 cursor-pointer">
                <input type="checkbox" name="privacy_notify_on_access" value="1" {{ $owner->privacy_notify_on_access ? 'checked' : '' }} class="mt-0.5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                <div>
                    <span class="text-sm font-semibold text-slate-800 block">Notify On Admin Access Events</span>
                    <span class="text-xs text-slate-500">Log and highlight all administrator interactions with your property documents and profiles.</span>
                </div>
            </label>

            <div class="pt-2">
                <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white font-semibold text-xs rounded-lg shadow transition">
                    Save Privacy Settings
                </button>
            </div>
        </form>
    </div>

    <!-- Immutable Privacy Audit Trail -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
        <h2 class="text-lg font-bold text-slate-800 mb-1">Privacy Access Audit Trail</h2>
        <p class="text-xs text-slate-500 mb-4">Immutable, tamper-evident log of all admin access requests, approvals, denials, and access operations.</p>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-600 uppercase text-xs font-semibold">
                    <tr>
                        <th class="px-4 py-2.5">Date & Time</th>
                        <th class="px-4 py-2.5">Admin User</th>
                        <th class="px-4 py-2.5">Action</th>
                        <th class="px-4 py-2.5">Resource</th>
                        <th class="px-4 py-2.5">Description</th>
                        <th class="px-4 py-2.5">IP Address</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 text-xs">
                    @forelse($auditLogs as $log)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 text-slate-500 font-mono">{{ $log->created_at->format('M d, Y h:i A') }}</td>
                            <td class="px-4 py-3 font-semibold text-slate-800">{{ $log->adminUser?->name ?? 'System' }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded font-mono font-bold uppercase text-[11px]
                                    @if(str_contains($log->action, 'approved')) bg-emerald-100 text-emerald-800
                                    @elseif(str_contains($log->action, 'denied')) bg-rose-100 text-rose-800
                                    @elseif(str_contains($log->action, 'revoked')) bg-amber-100 text-amber-800
                                    @else bg-slate-100 text-slate-700
                                    @endif">
                                    {{ $log->action }}
                                </span>
                            </td>
                            <td class="px-4 py-3 font-mono text-slate-600">{{ $log->resource }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $log->description }}</td>
                            <td class="px-4 py-3 text-slate-400 font-mono">{{ $log->ip_address ?? 'Local' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-6 text-slate-400">
                                No privacy audit records found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($auditLogs->hasPages())
            <div class="p-4 border-t border-slate-200">
                {{ $auditLogs->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

