@extends('layouts.owner')

@section('title', 'Create / Generate Billings')

@section('content')
<div class="space-y-6 max-w-5xl">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">New Rent Invoices & Billings</h1>
            <p class="text-sm text-slate-500">Generate batch monthly statements or create a custom manual invoice.</p>
        </div>
        <a href="{{ route('owner.billings.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-lg transition">
            &larr; Back to Invoices
        </a>
    </div>

    <!-- Batch Generator Card -->
    <div class="bg-gradient-to-r from-indigo-50 to-purple-50 border border-indigo-200 rounded-xl p-6 shadow-sm">
        <div class="flex items-start justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-lg bg-indigo-600 text-white flex items-center justify-center font-bold text-lg shadow">
                    ⚡
                </div>
                <div>
                    <h2 class="text-lg font-bold text-indigo-950">1-Click Batch Monthly Billing Generator</h2>
                    <p class="text-xs text-indigo-700">Generates invoices for all active contracts for the selected month. Automatically skips contracts that already have an invoice for that month.</p>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('owner.billings.generate-batch') }}" class="mt-5 flex flex-wrap items-end gap-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold uppercase text-indigo-900 mb-1">Billing Month</label>
                <input type="month" name="month" value="{{ date('Y-m') }}" required class="px-4 py-2 border border-indigo-300 rounded-lg bg-white text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm font-semibold">
            </div>

            <button type="submit" onclick="return confirm('Generate monthly billings for all active contracts in your properties?')" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm rounded-lg shadow transition flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                <span>Run Batch Generator ({{ $activeContracts->count() }} active contracts)</span>
            </button>
        </form>
    </div>

    <!-- Single Invoice Form Card -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6" x-data="{
        contracts: {{ json_encode($activeContracts->map(fn($c) => ['id' => $c->id, 'rent' => (float)$c->monthly_rent, 'due_day' => $c->due_day, 'tenant' => $c->tenant->full_name])) }},
        selectedContractId: '{{ old('contract_id', $activeContracts->first()?->id) }}',
        rent: {{ old('rent_amount', $activeContracts->first()?->monthly_rent ?? 0) }},
        prevBal: {{ old('previous_balance', 0) }},
        lateFee: {{ old('late_fee', 0) }},
        discount: {{ old('discount', 0) }},
        otherCharges: {{ old('other_charges', 0) }},
        total() {
            let sum = (parseFloat(this.rent) || 0) + (parseFloat(this.prevBal) || 0) + (parseFloat(this.lateFee) || 0) + (parseFloat(this.otherCharges) || 0) - (parseFloat(this.discount) || 0);
            return Math.max(0, sum).toFixed(2);
        },
        updateRent() {
            let found = this.contracts.find(c => c.id == this.selectedContractId);
            if (found) {
                this.rent = found.rent;
            }
        }
    }">
        <h2 class="text-lg font-bold text-slate-800 mb-1">Create Single Custom Invoice</h2>
        <p class="text-xs text-slate-500 mb-6">Create a one-off statement with customizable charges, discounts, or periods.</p>

        <form method="POST" action="{{ route('owner.billings.store') }}" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Tenant & Rental Contract *</label>
                    <select name="contract_id" x-model="selectedContractId" @change="updateRent()" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm bg-white focus:ring-2 focus:ring-indigo-500">
                        <option value="">-- Select Active Lease --</option>
                        @foreach($activeContracts as $c)
                            <option value="{{ $c->id }}">
                                {{ $c->tenant->full_name }} — {{ $c->property->name }} (Contract: {{ $c->contract_number }} | Standard Rent: ₱{{ number_format($c->monthly_rent, 2) }})
                            </option>
                        @endforeach
                    </select>
                    @error('contract_id')<p class="text-xs text-rose-500 mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Period Start *</label>
                    <input type="date" name="billing_period_start" value="{{ old('billing_period_start', date('Y-m-01')) }}" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
                    @error('billing_period_start')<p class="text-xs text-rose-500 mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Period End *</label>
                    <input type="date" name="billing_period_end" value="{{ old('billing_period_end', date('Y-m-t')) }}" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
                    @error('billing_period_end')<p class="text-xs text-rose-500 mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Due Date *</label>
                    <input type="date" name="due_date" value="{{ old('due_date', date('Y-m-05', strtotime('+1 month'))) }}" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
                    @error('due_date')<p class="text-xs text-rose-500 mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Monthly Rent Amount (₱) *</label>
                    <input type="number" step="0.01" min="0" name="rent_amount" x-model="rent" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
                    @error('rent_amount')<p class="text-xs text-rose-500 mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Previous Unpaid Balance (₱)</label>
                    <input type="number" step="0.01" min="0" name="previous_balance" x-model="prevBal" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Late Fee (₱)</label>
                    <input type="number" step="0.01" min="0" name="late_fee" x-model="lateFee" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Discount (₱)</label>
                    <input type="number" step="0.01" min="0" name="discount" x-model="discount" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Other Utility / Misc Charges (₱)</label>
                    <input type="number" step="0.01" min="0" name="other_charges" x-model="otherCharges" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Other Charges Description</label>
                    <input type="text" name="other_charges_description" value="{{ old('other_charges_description') }}" placeholder="e.g. Submetered Electricity (45 kWh) + Water share" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Notes / Reminders for Tenant</label>
                    <textarea name="notes" rows="2" placeholder="e.g. Please send GCash confirmation screenshot or pay at the main desk." class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">{{ old('notes') }}</textarea>
                </div>
            </div>

            <!-- Total Preview Banner -->
            <div class="p-4 bg-slate-900 text-white rounded-xl flex items-center justify-between">
                <div>
                    <div class="text-xs uppercase tracking-wider text-slate-400 font-bold">Total Statement Amount Due</div>
                    <div class="text-xs text-slate-300">Rent + Prev Balance + Late Fee + Other Charges - Discount</div>
                </div>
                <div class="text-3xl font-extrabold text-emerald-400">
                    ₱<span x-text="total()"></span>
                </div>
            </div>

            <div class="flex items-center justify-end space-x-3 pt-3">
                <a href="{{ route('owner.billings.index') }}" class="px-4 py-2 text-slate-600 hover:text-slate-800 text-sm font-semibold">Cancel</a>
                <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm rounded-lg shadow transition">
                    Issue Billing Statement
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

