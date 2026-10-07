@extends('layouts.admin')

@section('page_title', 'Owner Workspace: ' . ($owner->business_name ?? 'Owner #' . $owner->id))

@section('content')
<div class="space-y-6">

    {{-- Breadcrumb & Header --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <a href="{{ route('admin.owners.index') }}" class="text-xs text-blue-600 hover:underline mb-1 inline-block">← Back to House Owners</a>
            <h2 class="text-2xl font-bold text-gray-900">{{ $owner->business_name ?? 'House Owner Workspace' }}</h2>
            <p class="text-xs text-gray-500">Managed by {{ $owner->user?->name }} ({{ $owner->user?->email }})</p>
        </div>
        <div>
            <a href="{{ route('admin.owners.edit', $owner) }}"
               class="px-3.5 py-1.5 border border-gray-300 hover:bg-gray-50 text-gray-700 text-xs font-semibold rounded-lg">
                Edit Settings
            </a>
        </div>
    </div>

    {{-- Platform-Level Meta Card (Allowed for Admin) --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
        <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-3">Platform Subscription & Metadata</h3>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
            <div>
                <span class="text-xs text-gray-400 block">Subscription Tier</span>
                <span class="font-bold text-gray-900 uppercase">{{ $owner->subscription_plan }}</span>
            </div>
            <div>
                <span class="text-xs text-gray-400 block">Properties Registered</span>
                <span class="font-bold text-gray-900">{{ $owner->properties->count() }}</span>
            </div>
            <div>
                <span class="text-xs text-gray-400 block">Privacy Approval Required</span>
                <span class="font-semibold text-emerald-600">{{ $owner->privacy_require_approval ? 'Yes (Strict)' : 'No' }}</span>
            </div>
            <div>
                <span class="text-xs text-gray-400 block">Registered On</span>
                <span class="text-gray-700">{{ $owner->created_at->format('M d, Y') }}</span>
            </div>
        </div>
    </div>

    {{-- Privacy Status Banner --}}
    @if($activeSession)
        <div class="bg-amber-50 border border-amber-300 rounded-xl p-5 shadow-sm text-amber-950 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 font-bold text-sm text-amber-900 mb-1">
                    <span>🔓</span> ACTIVE TEMPORARY ACCESS SESSION
                </div>
                <p class="text-xs text-amber-800 leading-relaxed">
                    You have temporary read-only access granted by the owner. Expires in 
                    <strong class="font-mono">{{ $activeSession->expires_at->diffForHumans() }}</strong> 
                    ({{ $activeSession->expires_at->format('h:i A') }}).
                </p>
                <div class="flex flex-wrap gap-1 mt-2">
                    @foreach($activeSession->permissions as $p)
                        <span class="px-2 py-0.5 bg-amber-200/80 text-amber-950 rounded text-[10px] font-mono font-bold">{{ $p }}</span>
                    @endforeach
                </div>
            </div>
            <div class="shrink-0">
                <span class="px-3 py-1 bg-amber-200 text-amber-900 rounded-lg text-xs font-bold uppercase tracking-wider">
                    Read-Only Mode
                </span>
            </div>
        </div>
    @else
        <div class="bg-slate-900 rounded-xl p-6 text-white shadow-md flex flex-col md:flex-row md:items-center justify-between gap-5">
            <div>
                <div class="flex items-center gap-2 font-bold text-base text-amber-400 mb-1">
                    <span>🔒</span> PRIVATE OWNER BUSINESS DATA PROTECTED
                </div>
                <p class="text-xs text-slate-300 leading-relaxed max-w-xl">
                    By default, the Super Admin cannot view this owner's properties, tenants, contracts, financial revenues, or payment histories. To view protected data for auditing or technical support, you must request permission from the owner.
                </p>
            </div>
            <div>
                <a href="{{ route('admin.access-requests.create', ['owner_id' => $owner->id]) }}"
                   class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-bold tracking-wide uppercase shadow-sm transition inline-flex items-center gap-2">
                    <span>🛡️</span> Request Temporary Access
                </a>
            </div>
        </div>
    @endif

    {{-- Protected Sections --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- 1. Properties Section --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100 mb-4">
                <h3 class="font-bold text-gray-900 text-sm flex items-center gap-2">
                    <span>🏢</span> Properties & Units
                </h3>
                @if(!$hasPropertyAccess)
                    <span class="text-xs text-slate-400 font-mono">🔒 Locked</span>
                @else
                    <a href="{{ route('admin.protected.properties', $owner) }}" class="text-xs text-blue-600 font-semibold hover:underline">View full list →</a>
                @endif
            </div>

            @if($hasPropertyAccess)
                <div class="space-y-3">
                    @forelse($properties as $prop)
                        <div class="p-3 bg-gray-50 rounded-lg border border-gray-200">
                            <h4 class="font-bold text-sm text-gray-900">{{ $prop->name }} ({{ $prop->code }})</h4>
                            <p class="text-xs text-gray-500">{{ $prop->address }}, {{ $prop->municipality }}</p>
                            <p class="text-[11px] text-gray-400 mt-1">Rooms: {{ $prop->rooms->count() }} | Total Beds: {{ $prop->rooms->flatMap->beds->count() }}</p>
                        </div>
                    @empty
                        <p class="text-xs text-gray-400">No properties on file.</p>
                    @endforelse
                </div>
            @else
                <div class="p-6 bg-gray-50 rounded-lg text-center text-gray-400 text-xs">
                    <span class="text-xl block mb-1">🔒</span>
                    Property details, room numbers, and bed capacities are hidden.<br>
                    Requires <code class="font-mono text-gray-600">VIEW_PROPERTY</code> permission.
                </div>
            @endif
        </div>

        {{-- 2. Tenants Section --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100 mb-4">
                <h3 class="font-bold text-gray-900 text-sm flex items-center gap-2">
                    <span>👥</span> Tenant Profiles & Personal Info
                </h3>
                @if(!$hasTenantAccess)
                    <span class="text-xs text-slate-400 font-mono">🔒 Locked</span>
                @else
                    <a href="{{ route('admin.protected.tenants', $owner) }}" class="text-xs text-blue-600 font-semibold hover:underline">View full list →</a>
                @endif
            </div>

            @if($hasTenantAccess)
                <div class="space-y-3">
                    @forelse($tenants as $t)
                        <div class="p-3 bg-gray-50 rounded-lg border border-gray-200">
                            <h4 class="font-bold text-sm text-gray-900">{{ $t->full_name }}</h4>
                            <p class="text-xs text-gray-500">Contact: {{ $t->contact_number ?? 'Hidden/None' }} | Status: {{ ucfirst($t->status) }}</p>
                        </div>
                    @empty
                        <p class="text-xs text-gray-400">No tenants on file.</p>
                    @endforelse
                </div>
            @else
                <div class="p-6 bg-gray-50 rounded-lg text-center text-gray-400 text-xs">
                    <span class="text-xl block mb-1">🔒</span>
                    Tenant identities, government IDs, and contact numbers are protected.<br>
                    Requires <code class="font-mono text-gray-600">VIEW_TENANTS</code> permission.
                </div>
            @endif
        </div>

        {{-- 3. Financials & Payments --}}
        @php
            $hasBillingAccess = $activeSession && in_array('VIEW_BILLING', $activeSession->permissions, true);
            $hasContractAccess = $activeSession && in_array('VIEW_CONTRACTS', $activeSession->permissions, true);
        @endphp
        <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100 mb-4">
                <h3 class="font-bold text-gray-900 text-sm flex items-center gap-2">
                    <span>💰</span> Financials & Payments
                </h3>
                @if(!$hasPaymentAccess && !$hasBillingAccess)
                    <span class="text-xs text-slate-400 font-mono">🔒 Locked</span>
                @else
                    <span class="flex items-center gap-3 text-xs">
                        @if($hasBillingAccess)
                            <a href="{{ route('admin.protected.billings', $owner) }}" class="text-blue-600 font-semibold hover:underline">Billings →</a>
                        @endif
                        @if($hasPaymentAccess)
                            <a href="{{ route('admin.protected.payments', $owner) }}" class="text-blue-600 font-semibold hover:underline">Payments →</a>
                        @endif
                    </span>
                @endif
            </div>

            @if(!$hasPaymentAccess && !$hasBillingAccess)
                <div class="p-6 bg-gray-50 rounded-lg text-center text-gray-400 text-xs">
                    <span class="text-xl block mb-1">🔒</span>
                    Rent collections, billing statements, and income streams are strictly confidential.<br>
                    Requires <code class="font-mono text-gray-600">VIEW_PAYMENTS</code> or <code class="font-mono text-gray-600">VIEW_BILLING</code> permission.
                </div>
            @else
                <p class="text-xs text-gray-500">Temporary read-only access is active. Every view is recorded in the privacy audit log.</p>
            @endif
        </div>

        {{-- 4. Contracts & Private Documents --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100 mb-4">
                <h3 class="font-bold text-gray-900 text-sm flex items-center gap-2">
                    <span>📝</span> Leases & Private Documents
                </h3>
                @if(!$hasContractAccess)
                    <span class="text-xs text-slate-400 font-mono">🔒 Locked</span>
                @else
                    <a href="{{ route('admin.protected.contracts', $owner) }}" class="text-xs text-blue-600 font-semibold hover:underline">View contracts →</a>
                @endif
            </div>

            @if(!$hasContractAccess)
                <div class="p-6 bg-gray-50 rounded-lg text-center text-gray-400 text-xs">
                    <span class="text-xl block mb-1">🔒</span>
                    Lease contracts, terms, and private attachments are protected.<br>
                    Requires <code class="font-mono text-gray-600">VIEW_CONTRACTS</code> permission.
                </div>
            @else
                <p class="text-xs text-gray-500">Temporary read-only access is active. Every view is recorded in the privacy audit log.</p>
            @endif
        </div>

    </div>

</div>
@endsection

