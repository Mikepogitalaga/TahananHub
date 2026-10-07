@extends('layouts.owner')

@section('title', 'Record Expense')

@section('content')
<div class="space-y-6 max-w-2xl mx-auto">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Record New Expense</h1>
            <p class="text-sm text-slate-500">Log an operational cost or utility bill for your property.</p>
        </div>
        <a href="{{ route('owner.expenses.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-lg transition">
            &larr; Back
        </a>
    </div>

    <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
        <form method="POST" action="{{ route('owner.expenses.store') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Property *</label>
                    <select name="property_id" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm bg-white focus:ring-2 focus:ring-rose-500">
                        @foreach($properties as $prop)
                            <option value="{{ $prop->id }}" {{ old('property_id') == $prop->id ? 'selected' : '' }}>
                                {{ $prop->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('property_id')<p class="text-xs text-rose-500 mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Expense Category *</label>
                    <select name="category" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm bg-white focus:ring-2 focus:ring-rose-500">
                        <option value="electricity" {{ old('category') == 'electricity' ? 'selected' : '' }}>Electricity (Meralco/Coop)</option>
                        <option value="water" {{ old('category') == 'water' ? 'selected' : '' }}>Water Utility</option>
                        <option value="internet" {{ old('category') == 'internet' ? 'selected' : '' }}>Internet / WiFi</option>
                        <option value="maintenance" {{ old('category') == 'maintenance' ? 'selected' : '' }}>Preventive Maintenance</option>
                        <option value="repairs" {{ old('category') == 'repairs' ? 'selected' : '' }}>Repairs & Plumbing</option>
                        <option value="cleaning" {{ old('category') == 'cleaning' ? 'selected' : '' }}>Cleaning & Housekeeping</option>
                        <option value="supplies" {{ old('category') == 'supplies' ? 'selected' : '' }}>Supplies & Consumables</option>
                        <option value="furniture" {{ old('category') == 'furniture' ? 'selected' : '' }}>Furniture & Fixtures</option>
                        <option value="other" {{ old('category') == 'other' ? 'selected' : '' }}>Other Operating Expense</option>
                    </select>
                    @error('category')<p class="text-xs text-rose-500 mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Description / Item *</label>
                    <input type="text" name="description" value="{{ old('description') }}" required placeholder="e.g. Electric bill for August 2026 or Replacement bathroom faucets" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-rose-500">
                    @error('description')<p class="text-xs text-rose-500 mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Amount (₱) *</label>
                    <input type="number" step="0.01" min="0.01" name="amount" value="{{ old('amount') }}" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm font-bold text-rose-600 focus:ring-2 focus:ring-rose-500">
                    @error('amount')<p class="text-xs text-rose-500 mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Expense Date *</label>
                    <input type="date" name="expense_date" value="{{ old('expense_date', date('Y-m-d')) }}" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-rose-500">
                    @error('expense_date')<p class="text-xs text-rose-500 mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Payment Method *</label>
                    <select name="payment_method" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm bg-white focus:ring-2 focus:ring-rose-500">
                        <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                        <option value="gcash" {{ old('payment_method') == 'gcash' ? 'selected' : '' }}>GCash</option>
                        <option value="bank_transfer" {{ old('payment_method') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                        <option value="other" {{ old('payment_method') == 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Receipt Attachment (Photo/PDF)</label>
                    <input type="file" name="receipt" accept="image/*,application/pdf" class="w-full px-3 py-1.5 border border-slate-300 rounded-lg text-xs file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-xs file:bg-slate-100">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Additional Notes</label>
                    <textarea name="notes" rows="2" placeholder="Optional notes" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-rose-500">{{ old('notes') }}</textarea>
                </div>
            </div>

            <div class="flex items-center justify-end space-x-3 pt-4 border-t border-slate-100">
                <a href="{{ route('owner.expenses.index') }}" class="px-4 py-2 text-slate-600 hover:text-slate-800 text-sm font-semibold">Cancel</a>
                <button type="submit" class="px-6 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-semibold text-sm rounded-lg shadow transition">
                    Save Expense
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

