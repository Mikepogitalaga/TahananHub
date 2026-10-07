@extends('layouts.admin')

@section('page_title', 'Protected: Expenses')

@section('content')
<div class="space-y-6">
    @include('admin.protected._banner')

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-gray-100">
            <h3 class="font-bold text-gray-900 text-sm">🧾 Property Expenses <span class="text-[10px] font-mono text-gray-400 ml-2">VIEW_EXPENSES</span></h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs uppercase tracking-wider text-gray-500">
                    <tr>
                        <th class="text-left px-5 py-3">Expense #</th>
                        <th class="text-left px-5 py-3">Property</th>
                        <th class="text-left px-5 py-3">Category</th>
                        <th class="text-left px-5 py-3">Description</th>
                        <th class="text-right px-5 py-3">Amount</th>
                        <th class="text-left px-5 py-3">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($expenses as $e)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-3 font-mono text-xs text-gray-900">{{ $e->expense_number }}</td>
                            <td class="px-5 py-3 text-xs text-gray-600">{{ $e->property?->name }}</td>
                            <td class="px-5 py-3 text-xs text-gray-600 uppercase">{{ $e->category }}</td>
                            <td class="px-5 py-3 text-xs text-gray-700">{{ $e->description }}</td>
                            <td class="px-5 py-3 text-right font-semibold">₱{{ number_format($e->amount, 2) }}</td>
                            <td class="px-5 py-3 text-xs text-gray-600">{{ \Carbon\Carbon::parse($e->expense_date)->format('M d, Y') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-8 text-center text-gray-400 text-xs">No expense records.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">{{ $expenses->links() }}</div>
    </div>
</div>
@endsection
