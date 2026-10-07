@extends('layouts.owner')

@section('page_title', 'Tenant Profile: ' . $tenant->full_name)

@section('content')
<div class="space-y-6" x-data="{ assignModalOpen: false }">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <a href="{{ route('owner.tenants.index') }}" class="text-xs text-emerald-600 hover:underline mb-1 inline-block">← Back to Tenants</a>
            <div class="flex items-center gap-3">
                <h2 class="text-2xl font-bold text-gray-900">{{ $tenant->full_name }}</h2>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold
                    {{ $tenant->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-700' }}">
                    {{ ucfirst(str_replace('_', ' ', $tenant->status)) }}
                </span>
            </div>
            <p class="text-xs text-gray-500 mt-1">Tenant ID #{{ $tenant->id }} | Registered {{ $tenant->created_at->format('M d, Y') }}</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('owner.tenants.edit', $tenant) }}" class="px-3 py-2 border border-gray-300 rounded-lg text-xs font-semibold text-gray-700 hover:bg-gray-50">
                Edit Profile
            </a>

            @if($tenant->currentAssignment)
                <form method="POST" action="{{ route('owner.tenants.move-out', $tenant) }}"
                      onsubmit="return confirm('Confirm move out for {{ $tenant->full_name }}? Bed will be freed up.');">
                    @csrf
                    <button type="submit" class="px-3.5 py-2 bg-amber-500 hover:bg-amber-600 text-slate-950 rounded-lg text-xs font-bold shadow-sm">
                        Move Out Tenant
                    </button>
                </form>
            @else
                <button type="button" @click="assignModalOpen = true" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold shadow-sm">
                    Assign Bed Unit
                </button>
            @endif
        </div>
    </div>

    {{-- Top Grid: Profile & Current Assignment --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Left 2 cols: Profile Details --}}
        <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 p-6 shadow-sm space-y-5">
            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider">Tenant Profile & Details</h3>

            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-sm">
                <div>
                    <span class="text-xs text-gray-400 block">Contact Number</span>
                    <span class="font-medium text-gray-900">{{ $tenant->contact_number ?? '—' }}</span>
                </div>
                <div>
                    <span class="text-xs text-gray-400 block">Email Address</span>
                    <span class="font-medium text-gray-900">{{ $tenant->email ?? '—' }}</span>
                </div>
                <div>
                    <span class="text-xs text-gray-400 block">Gender / DOB</span>
                    <span class="text-gray-900">
                        {{ ucfirst($tenant->gender ?? 'N/A') }} 
                        @if($tenant->date_of_birth) ({{ $tenant->date_of_birth->format('M d, Y') }}) @endif
                    </span>
                </div>
            </div>

            <div class="border-t border-gray-100 pt-3">
                <span class="text-xs text-gray-400 block mb-0.5">Permanent Address</span>
                <span class="text-sm text-gray-800">{{ $tenant->address ?? 'No address on file' }}</span>
            </div>

            <div class="border-t border-gray-100 pt-3 grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
                <div>
                    <span class="text-xs text-gray-400 block">Emergency Contact</span>
                    <span class="font-medium text-gray-900">{{ $tenant->emergency_contact ?? '—' }}</span>
                </div>
                <div>
                    <span class="text-xs text-gray-400 block">Relationship</span>
                    <span class="text-gray-800">{{ $tenant->emergency_relationship ?? '—' }}</span>
                </div>
                <div>
                    <span class="text-xs text-gray-400 block">Emergency Phone</span>
                    <span class="font-medium text-gray-900">{{ $tenant->emergency_contact_number ?? '—' }}</span>
                </div>
            </div>

            <div class="border-t border-gray-100 pt-3 flex items-center justify-between text-xs">
                <div>
                    <span class="text-gray-400 block">Government ID Verification</span>
                    <span class="font-mono font-semibold text-gray-800">
                        {{ $tenant->gov_id_type ?? 'None on file' }}
                        @if($tenant->gov_id_number_encrypted)
                            • [Encrypted at Rest]
                        @endif
                    </span>
                </div>
                @if($tenant->user_id)
                    <span class="px-2.5 py-1 bg-indigo-50 text-indigo-700 rounded-md font-semibold text-[11px]">
                        ✓ Tenant Portal Account Linked
                    </span>
                @endif
            </div>
        </div>

        {{-- Right 1 col: Current Assignment Card --}}
        <div>
            @php $current = $tenant->currentAssignment; @endphp
            <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm space-y-4">
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider">Current Bed Assignment</h3>

                @if($current)
                    <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-xl space-y-2 text-xs">
                        <span class="text-[10px] font-bold text-emerald-800 uppercase tracking-wider block">Assigned Unit</span>
                        <h4 class="font-bold text-base text-emerald-950">{{ $current->property?->name }}</h4>
                        <div class="font-mono text-emerald-900 font-semibold">
                            Room {{ $current->room?->room_number }} – Bed {{ $current->bed?->bed_number }}
                        </div>
                        <p class="text-emerald-800 pt-1">
                            Monthly Rate: <strong>₱{{ number_format($current->bed?->rental_rate ?? $current->room?->rental_rate, 2) }}</strong>
                        </p>
                        <p class="text-emerald-700 text-[11px]">
                            Since: {{ $current->assigned_at->format('M d, Y') }} ({{ $current->assigned_at->diffForHumans() }})
                        </p>
                    </div>

                    <button type="button" @click="assignModalOpen = true" class="w-full py-2 border border-gray-300 rounded-lg text-xs font-semibold text-gray-700 hover:bg-gray-50">
                        Transfer to Another Bed
                    </button>
                @else
                    <div class="p-6 bg-gray-50 rounded-xl text-center text-xs text-gray-500 space-y-2">
                        <span class="text-2xl block">🛏️</span>
                        <p class="font-semibold text-gray-700">No Bed Assigned</p>
                        <p>Tenant is currently not occupying any bed.</p>
                        <button type="button" @click="assignModalOpen = true" class="mt-2 px-4 py-2 bg-emerald-600 text-white rounded-lg text-xs font-bold shadow-sm">
                            Assign Bed Now
                        </button>
                    </div>
                @endif
            </div>
        </div>

    </div>

    {{-- Historical Assignment Records (Requirement #21) --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm">
        <h3 class="font-bold text-gray-900 text-base mb-4">Rental & Assignment History</h3>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-gray-600">
                <thead class="bg-gray-50 text-[11px] uppercase font-semibold text-gray-500">
                    <tr>
                        <th class="py-2.5 px-3">Property</th>
                        <th class="py-2.5 px-3">Room & Bed</th>
                        <th class="py-2.5 px-3">Assigned Date</th>
                        <th class="py-2.5 px-3">Vacated Date</th>
                        <th class="py-2.5 px-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($tenant->tenantAssignments as $asgn)
                        <tr>
                            <td class="py-2.5 px-3 font-semibold text-gray-800">{{ $asgn->property?->name }}</td>
                            <td class="py-2.5 px-3 font-mono">Room {{ $asgn->room?->room_number }} – Bed {{ $asgn->bed?->bed_number }}</td>
                            <td class="py-2.5 px-3 text-gray-600">{{ $asgn->assigned_at->format('M d, Y') }}</td>
                            <td class="py-2.5 px-3 text-gray-600">{{ $asgn->vacated_at ? $asgn->vacated_at->format('M d, Y') : 'Active' }}</td>
                            <td class="py-2.5 px-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase
                                    {{ $asgn->is_current ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-600' }}">
                                    {{ $asgn->is_current ? 'Current' : 'Vacated' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-4 text-center text-gray-400">No rental history records.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Assign Bed Modal --}}
    <div x-show="assignModalOpen" x-cloak
         class="fixed inset-y-0 inset-x-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-gray-100 space-y-4"
             @click.away="assignModalOpen = false">
            
            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                <h3 class="font-bold text-gray-900 text-base">Assign Bed to {{ $tenant->full_name }}</h3>
                <button type="button" @click="assignModalOpen = false" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>

            <form method="POST" action="{{ route('owner.tenants.assign-bed', $tenant) }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Choose Available Bed *</label>
                    <select name="bed_id" required class="w-full px-3 py-2 border rounded-lg text-xs focus:ring-2 focus:ring-emerald-500">
                        <option value="">-- Select Bed --</option>
                        @foreach($availableProperties as $prop)
                            <optgroup label="{{ $prop->name }} ({{ $prop->code }})">
                                @foreach($prop->rooms as $room)
                                    @foreach($room->beds as $bed)
                                        <option value="{{ $bed->id }}">
                                            Room {{ $room->room_number }} – Bed {{ $bed->bed_number }} (₱{{ number_format($bed->rental_rate, 2) }}/mo)
                                        </option>
                                    @endforeach
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="assignModalOpen = false" class="px-4 py-2 border rounded-lg text-xs font-semibold text-gray-600">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold">Assign Bed</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

