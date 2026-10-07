@extends('layouts.admin')

@section('page_title', 'Activity & Privacy Audit Logs')

@section('content')
<div class="space-y-6" x-data="{ currentTab: '{{ $tab }}' }">

    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-900">System & Privacy Audit Trails</h2>
            <p class="text-sm text-gray-500">Immutable audit logs recording system actions and private data access.</p>
        </div>

        {{-- Tabs --}}
        <div class="bg-gray-100 p-1 rounded-xl flex gap-1">
            <button @click="currentTab = 'activity'" 
                    :class="currentTab === 'activity' ? 'bg-white shadow text-gray-900' : 'text-gray-600 hover:text-gray-900'"
                    class="px-4 py-1.5 rounded-lg text-xs font-semibold transition">
                Platform Activity Logs
            </button>
            <button @click="currentTab = 'privacy'" 
                    :class="currentTab === 'privacy' ? 'bg-white shadow text-gray-900' : 'text-gray-600 hover:text-gray-900'"
                    class="px-4 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1.5">
                <span>🛡️</span> Privacy Access Logs
            </button>
        </div>
    </div>

    {{-- TAB 1: Platform Activity Logs --}}
    <div x-show="currentTab === 'activity'" class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-600">
                <thead class="bg-gray-50 text-xs uppercase font-semibold text-gray-500 border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-3">Timestamp</th>
                        <th class="px-6 py-3">Actor</th>
                        <th class="px-6 py-3">Module</th>
                        <th class="px-6 py-3">Action</th>
                        <th class="px-6 py-3">Description / Label</th>
                        <th class="px-6 py-3">IP Address</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($activityLogs as $log)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-3.5 text-xs text-gray-400 font-mono">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                            <td class="px-6 py-3.5 font-medium text-gray-900 text-xs">{{ $log->user_name ?? 'System' }}</td>
                            <td class="px-6 py-3.5"><span class="px-2 py-0.5 bg-gray-100 rounded text-xs font-mono">{{ $log->module }}</span></td>
                            <td class="px-6 py-3.5 text-xs font-semibold text-gray-700">{{ $log->action }}</td>
                            <td class="px-6 py-3.5 text-xs text-gray-600 truncate max-w-xs">{{ $log->record_label ?? '—' }}</td>
                            <td class="px-6 py-3.5 text-xs text-gray-400 font-mono">{{ $log->ip_address ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-400 text-sm">No activity logs recorded.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($activityLogs->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">{{ $activityLogs->links() }}</div>
        @endif
    </div>

    {{-- TAB 2: Privacy Audit Logs --}}
    <div x-show="currentTab === 'privacy'" class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm" x-cloak>
        <div class="p-4 bg-amber-50 border-b border-amber-200 text-xs text-amber-900 flex items-center gap-2">
            <span>🔒</span>
            <span>These records document every access request, approval, denial, and protected resource inspection.</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-600">
                <thead class="bg-gray-50 text-xs uppercase font-semibold text-gray-500 border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-3">Timestamp</th>
                        <th class="px-6 py-3">Admin</th>
                        <th class="px-6 py-3">House Owner</th>
                        <th class="px-6 py-3">Event / Action</th>
                        <th class="px-6 py-3">Description</th>
                        <th class="px-6 py-3">IP Address</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($privacyLogs as $plog)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-3.5 text-xs text-gray-400 font-mono">{{ $plog->created_at->format('Y-m-d H:i:s') }}</td>
                            <td class="px-6 py-3.5 font-bold text-gray-900 text-xs">{{ $plog->adminUser?->name ?? 'Admin #' . $plog->admin_user_id }}</td>
                            <td class="px-6 py-3.5 text-xs text-gray-700">{{ $plog->houseOwner?->business_name ?? 'Owner #' . $plog->house_owner_id }}</td>
                            <td class="px-6 py-3.5">
                                <span class="px-2 py-0.5 rounded text-[11px] font-mono font-bold
                                    {{ str_contains($plog->action, 'approved') ? 'bg-emerald-100 text-emerald-800' : (str_contains($plog->action, 'denied') ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800') }}">
                                    {{ $plog->action }}
                                </span>
                            </td>
                            <td class="px-6 py-3.5 text-xs text-gray-600 max-w-sm">{{ $plog->description }}</td>
                            <td class="px-6 py-3.5 text-xs text-gray-400 font-mono">{{ $plog->ip_address }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-400 text-sm">No privacy audit events logged yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($privacyLogs->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">{{ $privacyLogs->links() }}</div>
        @endif
    </div>

</div>
@endsection

