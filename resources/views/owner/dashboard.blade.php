@extends('layouts.owner')

@section('page_title', 'Owner Workspace Dashboard')

@section('content')
<div class="space-y-6">

    {{-- Privacy Access Alert Banner if Admin requested permission --}}
    @if($pendingAdminRequests > 0)
        <div class="bg-amber-500 text-slate-950 p-4 rounded-xl shadow flex items-center justify-between gap-4 font-medium animate-pulse">
            <div class="flex items-center gap-3 text-sm">
                <span class="text-xl">⚠️</span>
                <span>
                    <strong>Platform Admin Access Request Pending:</strong> Super Admin has requested temporary permission to inspect data in your workspace.
                </span>
            </div>
            <a href="{{ route('owner.privacy.index') }}" class="px-3.5 py-1.5 bg-slate-950 text-white rounded-lg text-xs font-bold shrink-0 hover:bg-slate-900">
                Review Request →
            </a>
        </div>
    @endif

    {{-- Welcome Card --}}
    <div class="bg-gradient-to-r from-emerald-900 to-teal-900 rounded-2xl p-6 text-white shadow-md flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <span class="text-xs font-bold uppercase tracking-wider text-emerald-300">Workspace Overview</span>
            <h2 class="text-2xl font-bold tracking-tight mt-1">{{ $owner->business_name ?? 'My Boarding House' }}</h2>
            <p class="text-emerald-200 text-xs mt-1">
                Your private business data is protected and visible only to you.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('owner.billings.create') }}" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg text-xs font-bold shadow-sm">
                + Generate Billings
            </a>
            <a href="{{ route('owner.payments.create') }}" class="px-3.5 py-2 bg-white text-emerald-950 hover:bg-emerald-50 rounded-lg text-xs font-bold shadow-sm">
                + Record Payment
            </a>
        </div>
    </div>

    {{-- Operational KPIs --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
            <span class="text-[11px] text-gray-500 block font-medium">Properties</span>
            <span class="text-2xl font-extrabold text-gray-900">{{ $totalProperties }}</span>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
            <span class="text-[11px] text-gray-500 block font-medium">Total Rooms</span>
            <span class="text-2xl font-extrabold text-gray-900">{{ $totalRooms }}</span>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
            <span class="text-[11px] text-gray-500 block font-medium">Total Beds</span>
            <span class="text-2xl font-extrabold text-gray-900">{{ $totalBeds }}</span>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
            <span class="text-[11px] text-emerald-600 block font-medium">Occupied Beds</span>
            <span class="text-2xl font-extrabold text-emerald-600">{{ $occupiedBeds }}</span>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
            <span class="text-[11px] text-blue-600 block font-medium">Available Beds</span>
            <span class="text-2xl font-extrabold text-blue-600">{{ $availableBeds }}</span>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
            <span class="text-[11px] text-indigo-600 block font-medium">Active Tenants</span>
            <span class="text-2xl font-extrabold text-indigo-600">{{ $activeTenants }}</span>
        </div>
    </div>

    {{-- Financial KPIs for Month --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
            <span class="text-xs text-gray-500 block font-medium">Expected Rent (Mo.)</span>
            <p class="text-2xl font-extrabold text-gray-900 mt-1">₱{{ number_format($monthlyExpected, 2) }}</p>
            <span class="text-[10px] text-gray-400">From active contracts</span>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
            <span class="text-xs text-emerald-600 block font-medium">Collected Rent</span>
            <p class="text-2xl font-extrabold text-emerald-600 mt-1">₱{{ number_format($monthlyCollected, 2) }}</p>
            <span class="text-[10px] text-gray-400">Total received this month</span>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
            <span class="text-xs text-amber-600 block font-medium">Outstanding Balances</span>
            <p class="text-2xl font-extrabold text-amber-600 mt-1">₱{{ number_format($outstandingBalance, 2) }}</p>
            <span class="text-[10px] text-gray-400">Unpaid & overdue</span>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
            <span class="text-xs text-red-600 block font-medium">Monthly Expenses</span>
            <p class="text-2xl font-extrabold text-red-600 mt-1">₱{{ number_format($monthlyExpenses, 2) }}</p>
            <span class="text-[10px] text-gray-400">Utilities & repairs</span>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
            <span class="text-xs text-indigo-600 block font-medium">Net Income</span>
            <p class="text-2xl font-extrabold {{ $netIncome >= 0 ? 'text-indigo-600' : 'text-red-600' }} mt-1">
                ₱{{ number_format($netIncome, 2) }}
            </p>
            <span class="text-[10px] text-gray-400">Collected − Expenses</span>
        </div>
    </div>

    {{-- Two Columns: Recent Payments & Operational Status --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Recent Payments Table --}}
        <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100 mb-3">
                <h3 class="font-bold text-gray-900 text-sm">Recent Rent Payments</h3>
                <a href="{{ route('owner.payments.index') }}" class="text-xs text-emerald-600 font-semibold hover:underline">View All Payments →</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-gray-600">
                    <thead class="bg-gray-50 text-[11px] uppercase font-semibold text-gray-500">
                        <tr>
                            <th class="py-2.5 px-3">Receipt #</th>
                            <th class="py-2.5 px-3">Tenant</th>
                            <th class="py-2.5 px-3">Amount</th>
                            <th class="py-2.5 px-3">Method</th>
                            <th class="py-2.5 px-3">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($recentPayments as $payment)
                            <tr class="hover:bg-gray-50">
                                <td class="py-2.5 px-3 font-mono font-bold text-gray-800">{{ $payment->payment_number }}</td>
                                <td class="py-2.5 px-3 text-gray-900 font-medium">{{ $payment->tenant?->full_name }}</td>
                                <td class="py-2.5 px-3 font-bold text-emerald-600">₱{{ number_format($payment->amount, 2) }}</td>
                                <td class="py-2.5 px-3 uppercase text-[10px]">{{ $payment->payment_method }}</td>
                                <td class="py-2.5 px-3 text-gray-400">{{ $payment->payment_date->format('M d, Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-6 text-center text-gray-400">No payments recorded yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Maintenance & Quick Links --}}
        <div class="space-y-4">
            <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
                <h3 class="font-bold text-gray-900 text-sm mb-3">Maintenance Status</h3>
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <span class="text-xs text-gray-600">Pending Requests</span>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold {{ $pendingMaintenance > 0 ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-600' }}">
                        {{ $pendingMaintenance }}
                    </span>
                </div>
                <a href="{{ route('owner.maintenance.index') }}" class="mt-3 block text-center text-xs text-emerald-600 font-semibold hover:underline">
                    Manage Maintenance Requests →
                </a>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
                <h3 class="font-bold text-gray-900 text-sm mb-3">Privacy Guard</h3>
                <div class="text-xs text-gray-500 leading-relaxed space-y-2">
                    <p>🛡️ <strong>Zero-Trust Status:</strong> Active</p>
                    <p>Super Admin access is blocked by default at the database policy level.</p>
                </div>
                <a href="{{ route('owner.privacy.index') }}" class="mt-3 block text-center text-xs text-emerald-600 font-semibold hover:underline">
                    Manage Privacy & Access Sessions →
                </a>
            </div>
        </div>

    </div>

</div>
@endsection

