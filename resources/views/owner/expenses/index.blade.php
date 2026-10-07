@extends('layouts.owner')

@section('title', 'Operating Expenses')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Operating Expenses</h1>
            <p class="text-sm text-slate-500">Record utilities, maintenance, supplies, and repairs to calculate net profit.</p>
        </div>
        <a href="{{ route('owner.expenses.create') }}" class="inline-flex items-center space-x-2 px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-semibold text-sm rounded-lg shadow transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>Record New Expense</span>
        </a>
    </div>

    <!-- Filters & Total Bar -->
    <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <form method="GET" action="{{ route('owner.expenses.index') }}" class="flex flex-wrap items-center gap-3">
            <div>
                <select name="property_id" class="px-3 py-2 border border-slate-300 rounded-lg text-sm bg-white focus:ring-2 focus:ring-rose-500">
                    <option value="">-- All Properties --</option>
                    @foreach($properties as $prop)
                        <option value="{{ $prop->id }}" {{ request('property_id') == $prop->id ? 'selected' : '' }}>
                            {{ $prop->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <select name="category" class="px-3 py-2 border border-slate-300 rounded-lg text-sm bg-white focus:ring-2 focus:ring-rose-500">
                    <option value="">-- All Categories --</option>
                    <option value="electricity" {{ request('category') === 'electricity' ? 'selected' : '' }}>Electricity</option>
                    <option value="water" {{ request('category') === 'water' ? 'selected' : '' }}>Water</option>
                    <option value="internet" {{ request('category') === 'internet' ? 'selected' : '' }}>Internet</option>
                    <option value="maintenance" {{ request('category') === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                    <option value="repairs" {{ request('category') === 'repairs' ? 'selected' : '' }}>Repairs</option>
                    <option value="cleaning" {{ request('category') === 'cleaning' ? 'selected' : '' }}>Cleaning</option>
                    <option value="supplies" {{ request('category') === 'supplies' ? 'selected' : '' }}>Supplies</option>
                    <option value="furniture" {{ request('category') === 'furniture' ? 'selected' : '' }}>Furniture</option>
                    <option value="other" {{ request('category') === 'other' ? 'selected' : '' }}>Other</option>
                </select>
            </div>
            <div>
                <input type="month" name="month" value="{{ request('month') }}" class="px-3 py-2 border border-slate-300 rounded-lg text-sm bg-white focus:ring-2 focus:ring-rose-500">
            </div>
            <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white font-semibold text-sm rounded-lg transition">Filter</button>
            @if(request()->anyFilled(['property_id', 'category', 'month']))
                <a href="{{ route('owner.expenses.index') }}" class="text-xs text-slate-500 hover:underline">Reset</a>
            @endif
        </form>

        <div class="text-right">
            <span class="text-xs uppercase font-bold text-slate-400 block">Total Filtered Expenses</span>
            <span class="text-2xl font-black text-rose-600">₱{{ number_format($totalExpensesAmount, 2) }}</span>
        </div>
    </div>

    <!-- Expenses Table -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 uppercase text-xs">
                    <tr>
                        <th class="px-5 py-3">Expense #</th>
                        <th class="px-5 py-3">Date</th>
                        <th class="px-5 py-3">Property</th>
                        <th class="px-5 py-3">Category</th>
                        <th class="px-5 py-3">Description</th>
                        <th class="px-5 py-3">Method</th>
                        <th class="px-5 py-3 text-right">Amount</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($expenses as $e)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-3.5 font-mono text-xs font-semibold text-slate-700">{{ $e->expense_number }}</td>
                            <td class="px-5 py-3.5 text-slate-600">{{ \Carbon\Carbon::parse($e->expense_date)->format('M d, Y') }}</td>
                            <td class="px-5 py-3.5 font-medium text-slate-800">{{ $e->property->name }}</td>
                            <td class="px-5 py-3.5">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold uppercase bg-slate-100 text-slate-700">
                                    {{ $e->category }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-slate-700">
                                {{ $e->description }}
                                @if($e->notes)
                                    <div class="text-xs text-slate-400 italic">{{ $e->notes }}</div>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 uppercase text-xs text-slate-500">{{ $e->payment_method }}</td>
                            <td class="px-5 py-3.5 text-right font-extrabold text-rose-600 text-base">
                                ₱{{ number_format($e->amount, 2) }}
                            </td>
                            <td class="px-5 py-3.5 text-right space-x-2">
                                <a href="{{ route('owner.expenses.edit', $e) }}" class="text-xs text-indigo-600 hover:underline font-semibold">Edit</a>
                                <form method="POST" action="{{ route('owner.expenses.destroy', $e) }}" class="inline" onsubmit="return confirm('Delete this expense?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs text-rose-600 hover:underline font-semibold">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-10 text-slate-400">
                                No expense records found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($expenses->hasPages())
            <div class="p-4 border-t border-slate-200">
                {{ $expenses->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

