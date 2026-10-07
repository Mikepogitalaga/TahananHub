@extends('layouts.owner')

@section('page_title', 'Edit Contract: ' . $contract->contract_number)

@section('content')
<div class="max-w-3xl mx-auto bg-white rounded-xl border border-gray-200 p-6 shadow-sm">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Edit Contract Terms</h2>
            <p class="text-sm text-gray-500">{{ $contract->contract_number }} – Tenant: {{ $contract->tenant?->full_name }}</p>
        </div>
        <form method="POST" action="{{ route('owner.contracts.destroy', $contract) }}"
              onsubmit="return confirm('Delete this contract?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="text-xs text-red-600 hover:text-red-700 font-semibold p-2">Delete Contract</button>
        </form>
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

    <form method="POST" action="{{ route('owner.contracts.update', $contract) }}" class="space-y-4">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Monthly Rent (₱) *</label>
                <input type="number" step="0.01" name="monthly_rent" value="{{ old('monthly_rent', $contract->monthly_rent) }}" required
                       class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 font-bold text-emerald-800">
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Security Deposit (₱)</label>
                <input type="number" step="0.01" name="security_deposit" value="{{ old('security_deposit', $contract->security_deposit) }}"
                       class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-emerald-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Advance Payment (₱)</label>
                <input type="number" step="0.01" name="advance_payment" value="{{ old('advance_payment', $contract->advance_payment) }}"
                       class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-emerald-500">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Start Date *</label>
                <input type="date" name="start_date" value="{{ old('start_date', $contract->start_date->format('Y-m-d')) }}" required
                       class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-emerald-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">End Date</label>
                <input type="date" name="end_date" value="{{ old('end_date', $contract->end_date?->format('Y-m-d')) }}"
                       class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-emerald-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Due Day of Month *</label>
                <input type="number" min="1" max="31" name="due_day" value="{{ old('due_day', $contract->due_day) }}" required
                       class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-emerald-500">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Late Fee (₱)</label>
                <input type="number" step="0.01" name="late_fee" value="{{ old('late_fee', $contract->late_fee) }}"
                       class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-emerald-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Contract Status *</label>
                <select name="status" required class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-emerald-500">
                    <option value="active" {{ old('status', $contract->status) === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="pending" {{ old('status', $contract->status) === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="expired" {{ old('status', $contract->status) === 'expired' ? 'selected' : '' }}>Expired</option>
                    <option value="terminated" {{ old('status', $contract->status) === 'terminated' ? 'selected' : '' }}>Terminated</option>
                </select>
            </div>
        </div>

        <div>
            <label class="block text-xs font-semibold text-gray-700 mb-1">Terms & Conditions</label>
            <textarea name="terms" rows="3"
                      class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-emerald-500">{{ old('terms', $contract->terms) }}</textarea>
        </div>

        <div class="pt-4 flex items-center justify-end gap-3 border-t border-gray-100">
            <a href="{{ route('owner.contracts.show', $contract) }}" class="px-4 py-2 border rounded-lg text-sm text-gray-700 hover:bg-gray-50">Cancel</a>
            <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-semibold shadow-sm">
                Update Contract
            </button>
        </div>
    </form>
</div>
@endsection

