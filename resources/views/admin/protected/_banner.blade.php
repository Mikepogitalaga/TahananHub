@php
    $titles = [
        'properties' => 'Properties & Units',
        'rooms'      => 'Rooms & Capacities',
        'tenants'    => 'Tenant Profiles',
        'contracts'  => 'Rental Contracts',
        'billings'   => 'Rent Billing Statements',
        'payments'   => 'Payment Records',
        'expenses'   => 'Property Expenses',
    ];
@endphp
<div class="bg-amber-50 border border-amber-300 rounded-xl p-4 shadow-sm text-amber-950 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
    <div>
        <div class="flex items-center gap-2 font-bold text-sm text-amber-900 mb-1">
            <span>🔓</span> ACTIVE TEMPORARY ACCESS — {{ $titles[$section] ?? strtoupper($section) }}
        </div>
        <p class="text-xs text-amber-800 leading-relaxed">
            Owner: <strong>{{ $owner->business_name ?? 'Owner #' . $owner->id }}</strong> ·
            Session expires <strong class="font-mono">{{ $activeSession->expires_at->format('M d, Y h:i A') }}</strong>
            ({{ $activeSession->expires_at->diffForHumans() }}) ·
            This view is <strong>READ ONLY</strong>. All access is audit-logged.
        </p>
        <div class="flex flex-wrap gap-1 mt-2">
            @foreach($activeSession->permissions as $p)
                <span class="px-2 py-0.5 bg-amber-200/80 text-amber-950 rounded text-[10px] font-mono font-bold">{{ $p }}</span>
            @endforeach
        </div>
    </div>
    <a href="{{ route('admin.owners.show', $owner) }}" class="shrink-0 px-3 py-1.5 bg-white border border-amber-400 text-amber-900 rounded-lg text-xs font-semibold hover:bg-amber-100">
        ← Back to Owner
    </a>
</div>
