@extends('layouts.owner')

@section('title', 'Financial & Operational Reports')

@section('content')
<div class="space-y-6">
    <!-- Header with Filters & Print -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Business Reports & Analytics</h1>
            <p class="text-sm text-slate-500">Comprehensive income statements, occupancy performance, and receivables.</p>
        </div>

        <div class="flex items-center space-x-3">
            <button onclick="window.print()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-lg transition flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Print Report</span>
            </button>

            <form method="GET" action="{{ route('owner.reports.index') }}" class="flex items-center space-x-2">
                <select name="month" class="px-3 py-2 border border-slate-300 rounded-lg text-sm bg-white font-medium">
                    @for($m = 1; $m <= 12; $m++)
                        @php $val = str_pad((string)$m, 2, '0', STR_PAD_LEFT); @endphp
                        <option value="{{ $val }}" {{ (int)$month === $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create(null, $m, 1)->format('F') }}
                        </option>
                    @endfor
                </select>

                <select name="year" class="px-3 py-2 border border-slate-300 rounded-lg text-sm bg-white font-medium">
                    @for($y = date('Y') - 2; $y <= date('Y') + 1; $y++)
                        <option value="{{ $y }}" {{ (int)$year === $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>

                <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm rounded-lg transition">Apply</button>
            </form>
        </div>
    </div>

    <!-- Monthly Overview Highlights -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm">
            <span class="text-xs uppercase font-bold text-slate-400 block">Total Collections</span>
            <div class="text-2xl font-black text-emerald-600 mt-1">₱{{ number_format($monthlyCollections, 2) }}</div>
            <span class="text-xs text-slate-400 mt-1 block">For {{ \Carbon\Carbon::create($year, (int)$month, 1)->format('F Y') }}</span>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm">
            <span class="text-xs uppercase font-bold text-slate-400 block">Operating Expenses</span>
            <div class="text-2xl font-black text-rose-600 mt-1">₱{{ number_format($monthlyExpenses, 2) }}</div>
            <span class="text-xs text-slate-400 mt-1 block">Utilities, repairs & supplies</span>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm">
            <span class="text-xs uppercase font-bold text-slate-400 block">Net Operating Profit</span>
            <div class="text-2xl font-black {{ $netProfit >= 0 ? 'text-indigo-600' : 'text-rose-600' }} mt-1">
                ₱{{ number_format($netProfit, 2) }}
            </div>
            <span class="text-xs {{ $netProfit >= 0 ? 'text-emerald-600 font-semibold' : 'text-rose-600 font-semibold' }} mt-1 block">
                {{ $netProfit >= 0 ? 'Profitable' : 'Deficit / Loss' }}
            </span>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm">
            <span class="text-xs uppercase font-bold text-slate-400 block">Current Occupancy</span>
            <div class="text-2xl font-black text-indigo-950 mt-1">{{ $occupancyRate }}%</div>
            <span class="text-xs text-slate-500 mt-1 block">{{ $occupiedBeds }} of {{ $totalBeds }} beds occupied</span>
        </div>
    </div>

    <!-- Annual 12-Month Performance Table -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
        <h2 class="text-lg font-bold text-slate-800 mb-1">Annual Cash Flow Summary ({{ $year }})</h2>
        <p class="text-xs text-slate-500 mb-6">Monthly rent collection vs operating expenses breakdown.</p>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-600 uppercase text-xs font-semibold">
                    <tr>
                        <th class="px-4 py-3">Month</th>
                        <th class="px-4 py-3 text-right">Collections (₱)</th>
                        <th class="px-4 py-3 text-right">Expenses (₱)</th>
                        <th class="px-4 py-3 text-right">Net Profit (₱)</th>
                        <th class="px-4 py-3 text-right">Margin</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach($monthlyBreakdown as $b)
                        @php
                            $margin = $b['collections'] > 0 ? round(($b['net'] / $b['collections']) * 100, 1) : 0;
                        @endphp
                        <tr class="hover:bg-slate-50 {{ (int)$month === $b['month_num'] ? 'bg-indigo-50/50 font-semibold' : '' }}">
                            <td class="px-4 py-3 text-slate-800">{{ $b['month_name'] }}</td>
                            <td class="px-4 py-3 text-right text-emerald-600 font-semibold">₱{{ number_format($b['collections'], 2) }}</td>
                            <td class="px-4 py-3 text-right text-rose-600 font-semibold">₱{{ number_format($b['expenses'], 2) }}</td>
                            <td class="px-4 py-3 text-right font-bold {{ $b['net'] >= 0 ? 'text-indigo-700' : 'text-rose-600' }}">
                                ₱{{ number_format($b['net'], 2) }}
                            </td>
                            <td class="px-4 py-3 text-right text-xs text-slate-500">
                                @if($b['collections'] > 0)
                                    <span class="px-2 py-0.5 rounded-full {{ $margin >= 0 ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                        {{ $margin }}%
                                    </span>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-slate-50 font-bold border-t border-slate-200">
                    <tr>
                        <th class="px-4 py-3 text-slate-800">Total ({{ $year }})</th>
                        <th class="px-4 py-3 text-right text-emerald-600">₱{{ number_format(collect($monthlyBreakdown)->sum('collections'), 2) }}</th>
                        <th class="px-4 py-3 text-right text-rose-600">₱{{ number_format(collect($monthlyBreakdown)->sum('expenses'), 2) }}</th>
                        <th class="px-4 py-3 text-right text-indigo-700">₱{{ number_format(collect($monthlyBreakdown)->sum('net'), 2) }}</th>
                        <th></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Outstanding Receivables / Tenant Balances -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-lg font-bold text-slate-800">Uncollected Rent & Receivables</h2>
                <p class="text-xs text-slate-500">Tenants with overdue or partial balances.</p>
            </div>
            <div class="text-right">
                <span class="text-xs uppercase font-bold text-slate-400 block">Total Delinquent Balance</span>
                <span class="text-xl font-black text-rose-600">₱{{ number_format($totalUnpaidBalance, 2) }}</span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-600 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-2.5">Invoice #</th>
                        <th class="px-4 py-2.5">Tenant</th>
                        <th class="px-4 py-2.5">Property</th>
                        <th class="px-4 py-2.5">Due Date</th>
                        <th class="px-4 py-2.5">Status</th>
                        <th class="px-4 py-2.5 text-right">Balance Due</th>
                        <th class="px-4 py-2.5 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($unpaidBillings as $ub)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 font-mono font-bold text-indigo-600 text-xs">{{ $ub->billing_number }}</td>
                            <td class="px-4 py-3 font-medium text-slate-800">{{ $ub->tenant->full_name }}</td>
                            <td class="px-4 py-3 text-slate-600 text-xs">{{ $ub->contract->property->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-rose-600 font-semibold text-xs">{{ \Carbon\Carbon::parse($ub->due_date)->format('M d, Y') }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded-full text-xs font-bold uppercase {{ $ub->status === 'overdue' ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800' }}">
                                    {{ $ub->status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right font-black text-rose-600">₱{{ number_format($ub->balance, 2) }}</td>
                            <td class="px-4 py-3 text-right space-x-2">
                                <a href="{{ route('owner.payments.create', ['billing_id' => $ub->id]) }}" class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded text-xs font-semibold">
                                    Record Payment
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-6 text-slate-400">
                                Excellent! All tenant invoices are completely up to date.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

