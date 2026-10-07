@extends('layouts.owner')

@section('title', 'Edit Expense ' . $expense->expense_number)

@section('content')
<div class="space-y-6 max-w-2xl mx-auto">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Edit Expense</h1>
            <p class="text-sm text-slate-500">{{ $expense->expense_number }}</p>
        </div>
        <a href="{{ route('owner.expenses.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-lg transition">
            &larr; Back
        </a>
    </div>

    <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
        <form method="POST" action="{{ route('owner.expenses.update', $expense) }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Property *</label>
                    <select name="property_id" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm bg-white focus:ring-2 focus:ring-rose-500">
                        @foreach($properties as $prop)
                            <option value="{{ $prop->id }}" {{ old('property_id', $expense->property_id) == $prop->id ? 'selected' : '' }}>
                                {{ $prop->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Expense Category *</label>
                    <select name="category" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm bg-white focus:ring-2 focus:ring-rose-500">
                        @php
                            $cats = ['electricity' => 'Electricity', 'water' => 'Water', 'internet' => 'Internet', 'maintenance' => 'Maintenance', 'repairs' => 'Repairs', 'cleaning' => 'Cleaning', 'supplies' => 'Supplies', 'furniture' => 'Furniture', 'other' => 'Other'];
                        @endphp
                        @foreach($cats as $val => $lbl)
                            <option value="{{ $val }}" {{ old('category', $expense->category) === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Description / Item *</label>
                    <input type="text" name="description" value="{{ old('description', $expense->description) }}" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-rose-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Amount (₱) *</label>
                    <input type="number" step="0.01" min="0.01" name="amount" value="{{ old('amount', $expense->amount) }}" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm font-bold text-rose-600 focus:ring-2 focus:ring-rose-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Expense Date *</label>
                    <input type="date" name="expense_date" value="{{ old('expense_date', $expense->expense_date) }}" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-rose-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Payment Method *</label>
                    <select name="payment_method" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm bg-white focus:ring-2 focus:ring-rose-500">
                        @foreach(['cash' => 'Cash', 'gcash' => 'GCash', 'bank_transfer' => 'Bank Transfer', 'other' => 'Other'] as $k => $v)
                            <option value="{{ $k }}" {{ old('payment_method', $expense->payment_method) === $k ? 'selected' : '' }}>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Receipt Attachment</label>
                    <input type="file" name="receipt" accept="image/*,application/pdf" class="w-full px-3 py-1.5 border border-slate-300 rounded-lg text-xs file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-xs file:bg-slate-100">
                    @if($expense->receipt_path)
                        <p class="text-xs text-emerald-600 mt-1">Has existing receipt attached.</p>
                    @endif
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Additional Notes</label>
                    <textarea name="notes" rows="2" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-rose-500">{{ old('notes', $expense->notes) }}</textarea>
                </div>
            </div>

            <div class="flex items-center justify-end space-x-3 pt-4 border-t border-slate-100">
                <a href="{{ route('owner.expenses.index') }}" class="px-4 py-2 text-slate-600 hover:text-slate-800 text-sm font-semibold">Cancel</a>
                <button type="submit" class="px-6 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-semibold text-sm rounded-lg shadow transition">
                    Update Expense
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

