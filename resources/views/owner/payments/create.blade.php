@extends('layouts.owner')

@section('title', 'Record Payment')

@section('content')
<div class="space-y-6 max-w-2xl mx-auto" x-data="{
    billings: {{ json_encode($unpaidBillings->map(fn($b) => [
        'id' => $b->id,
        'number' => $b->billing_number,
        'tenant' => $b->tenant->full_name,
        'balance' => (float)$b->balance,
        'total' => (float)$b->total_amount,
        'property' => $b->contract->property->name ?? ''
    ])) }},
    selectedId: '{{ old('billing_id', $selectedBillingId ?? ($unpaidBillings->first()?->id ?? '')) }}',
    amount: '{{ old('amount', '') }}',
    method: '{{ old('payment_method', 'cash') }}',
    getSelected() {
        return this.billings.find(b => b.id == this.selectedId) || null;
    },
    setFullBalance() {
        let sel = this.getSelected();
        if (sel) {
            this.amount = sel.balance.toFixed(2);
        }
    }
}" x-init="if (!amount && getSelected()) { amount = getSelected().balance.toFixed(2); }">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Record Payment</h1>
            <p class="text-sm text-slate-500">Apply a collection receipt to an outstanding tenant invoice.</p>
        </div>
        <a href="{{ route('owner.payments.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-lg transition">
            &larr; Back
        </a>
    </div>

    @if($unpaidBillings->isEmpty())
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-6 text-center text-amber-900">
            <h3 class="font-bold text-base">No Outstanding Invoices</h3>
            <p class="text-xs text-amber-700 mt-1">There are currently no unpaid or partially paid billing statements awaiting collection.</p>
            <div class="mt-4">
                <a href="{{ route('owner.billings.create') }}" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-lg text-sm font-semibold">
                    Generate New Invoices
                </a>
            </div>
        </div>
    @else
        <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
            <form method="POST" action="{{ route('owner.payments.store') }}" class="space-y-5">
                @csrf

                <!-- Select Invoice -->
                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Invoice / Statement of Account *</label>
                    <select name="billing_id" x-model="selectedId" @change="setFullBalance()" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm bg-white focus:ring-2 focus:ring-emerald-500">
                        <option value="">-- Choose Outstanding Invoice --</option>
                        @foreach($unpaidBillings as $b)
                            <option value="{{ $b->id }}">
                                {{ $b->billing_number }} &bull; {{ $b->tenant->full_name }} &bull; Balance: ₱{{ number_format($b->balance, 2) }} ({{ $b->contract->property->name ?? '' }})
                            </option>
                        @endforeach
                    </select>
                    @error('billing_id')<p class="text-xs text-rose-500 mt-1">{{ $message }}</p>@enderror
                </div>

                <!-- Outstanding Card Preview -->
                <div x-show="getSelected()" class="p-4 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between">
                    <div>
                        <div class="text-xs uppercase font-bold text-slate-400">Current Outstanding Balance</div>
                        <div class="text-sm font-medium text-slate-700" x-text="getSelected() ? getSelected().tenant : ''"></div>
                    </div>
                    <div class="text-right">
                        <div class="text-2xl font-black text-rose-600">
                            ₱<span x-text="getSelected() ? getSelected().balance.toFixed(2) : '0.00'"></span>
                        </div>
                        <button type="button" @click="setFullBalance()" class="text-xs text-indigo-600 hover:underline font-semibold">
                            Pay Exact Balance
                        </button>
                    </div>
                </div>

                <!-- Payment Details -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Payment Amount (₱) *</label>
                        <input type="number" step="0.01" min="0.01" name="amount" x-model="amount" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 font-semibold text-lg text-emerald-700">
                        @error('amount')<p class="text-xs text-rose-500 mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Payment Date *</label>
                        <input type="date" name="payment_date" value="{{ old('payment_date', date('Y-m-d')) }}" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500">
                        @error('payment_date')<p class="text-xs text-rose-500 mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Payment Method *</label>
                        <select name="payment_method" x-model="method" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm bg-white focus:ring-2 focus:ring-emerald-500">
                            <option value="cash">Cash</option>
                            <option value="gcash">GCash</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="online_payment">Online Payment</option>
                            <option value="other">Other</option>
                        </select>
                        @error('payment_method')<p class="text-xs text-rose-500 mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">
                            Reference / Trans # <span x-show="method !== 'cash'" class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="reference_number" value="{{ old('reference_number') }}" placeholder="e.g. GCash Ref 1002934882" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500">
                        @error('reference_number')<p class="text-xs text-rose-500 mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Remarks / Note</label>
                        <textarea name="notes" rows="2" placeholder="Optional notes (e.g. Paid in front office)" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500">{{ old('notes') }}</textarea>
                    </div>
                </div>

                <div class="flex items-center justify-end space-x-3 pt-4 border-t border-slate-100">
                    <a href="{{ route('owner.payments.index') }}" class="px-4 py-2 text-slate-600 hover:text-slate-800 text-sm font-semibold">Cancel</a>
                    <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-sm rounded-lg shadow transition">
                        Save & Issue Receipt
                    </button>
                </div>
            </form>
        </div>
    @endif
</div>
@endsection

