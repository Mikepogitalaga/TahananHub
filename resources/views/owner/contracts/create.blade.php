@extends('layouts.owner')

@section('page_title', 'Create Rental Contract')

@section('content')
<div class="max-w-3xl mx-auto bg-white rounded-xl border border-gray-200 p-6 shadow-sm">
    <div class="mb-6">
        <h2 class="text-xl font-bold text-gray-900">New Rental Contract</h2>
        <p class="text-sm text-gray-500">Establish legal lease terms, monthly rental rates, security deposits, and due dates.</p>
    </div>

    @if($errors->any())
        <div class="mb-5 p-4 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('owner.contracts.store') }}" class="space-y-5">
        @csrf

        {{-- Tenant Selection --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Select Tenant *</label>
            <select name="tenant_id" required class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-emerald-500">
                <option value="">-- Choose Tenant --</option>
                @foreach($tenants as $t)
                    <option value="{{ $t->id }}" {{ (old('tenant_id', $selectedTenantId) == $t->id) ? 'selected' : '' }}>
                        {{ $t->full_name }} ({{ $t->contact_number ?? $t->email }})
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Unit Selection --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Select Property, Room & Bed Unit *</label>
            <select name="bed_unit" id="bedUnitSelect" required onchange="handleUnitSelect(this)"
                    class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-emerald-500">
                <option value="">-- Choose Unit --</option>
                @foreach($properties as $prop)
                    <optgroup label="{{ $prop->name }} ({{ $prop->code }})">
                        @foreach($prop->rooms as $room)
                            @foreach($room->beds as $bed)
                                <option value="{{ $bed->id }}" 
                                        data-property="{{ $prop->id }}"
                                        data-room="{{ $room->id }}"
                                        data-bed="{{ $bed->id }}"
                                        data-rate="{{ $bed->rental_rate ?? $room->rental_rate }}">
                                    {{ $prop->name }} › Room {{ $room->room_number }} – Bed {{ $bed->bed_number }} (₱{{ number_format($bed->rental_rate ?? $room->rental_rate, 2) }}/mo)
                                </option>
                            @endforeach
                        @endforeach
                    </optgroup>
                @endforeach
            </select>

            {{-- Hidden fields populated by JS --}}
            <input type="hidden" name="property_id" id="hiddenPropertyId" value="{{ old('property_id') }}">
            <input type="hidden" name="room_id" id="hiddenRoomId" value="{{ old('room_id') }}">
            <input type="hidden" name="bed_id" id="hiddenBedId" value="{{ old('bed_id') }}">
        </div>

        {{-- Financial Terms --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2">
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Monthly Rent (₱) *</label>
                <input type="number" step="0.01" name="monthly_rent" id="monthlyRentInput" value="{{ old('monthly_rent', 3000) }}" required
                       class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 font-bold text-emerald-800">
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Security Deposit (₱)</label>
                <input type="number" step="0.01" name="security_deposit" value="{{ old('security_deposit', 0) }}"
                       class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-emerald-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Advance Payment (₱)</label>
                <input type="number" step="0.01" name="advance_payment" value="{{ old('advance_payment', 0) }}"
                       class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-emerald-500">
            </div>
        </div>

        {{-- Schedule & Dates --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Start Date *</label>
                <input type="date" name="start_date" value="{{ old('start_date', date('Y-m-d')) }}" required
                       class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-emerald-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">End Date (Optional)</label>
                <input type="date" name="end_date" value="{{ old('end_date') }}"
                       class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-emerald-500">
                <span class="text-[10px] text-gray-400">Leave blank for month-to-month</span>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Due Day of Month *</label>
                <input type="number" min="1" max="31" name="due_day" value="{{ old('due_day', 5) }}" required
                       class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-emerald-500">
                <span class="text-[10px] text-gray-400">e.g. 5 = 5th day of every month</span>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Late Fee (₱ / month)</label>
                <input type="number" step="0.01" name="late_fee" value="{{ old('late_fee', 100) }}"
                       class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-emerald-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Initial Status *</label>
                <select name="status" required class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-emerald-500">
                    <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="pending" {{ old('status') === 'pending' ? 'selected' : '' }}>Pending Signature</option>
                </select>
            </div>
        </div>

        <div>
            <label class="block text-xs font-semibold text-gray-700 mb-1">Terms & Conditions / House Rules</label>
            <textarea name="terms" rows="3"
                      placeholder="Curfew, visitors policy, cooking rules, electricity surcharge limits..."
                      class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-emerald-500">{{ old('terms') }}</textarea>
        </div>

        <div class="pt-4 flex items-center justify-end gap-3 border-t border-gray-100">
            <a href="{{ route('owner.contracts.index') }}" class="px-4 py-2 border rounded-lg text-sm text-gray-700 hover:bg-gray-50">Cancel</a>
            <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-semibold shadow-sm">
                Create Contract
            </button>
        </div>
    </form>
</div>

<script>
    function handleUnitSelect(select) {
        var opt = select.options[select.selectedIndex];
        if (opt && opt.dataset.property) {
            document.getElementById('hiddenPropertyId').value = opt.dataset.property;
            document.getElementById('hiddenRoomId').value = opt.dataset.room;
            document.getElementById('hiddenBedId').value = opt.dataset.bed;
            if (opt.dataset.rate) {
                document.getElementById('monthlyRentInput').value = opt.dataset.rate;
            }
        }
    }
</script>
@endsection

