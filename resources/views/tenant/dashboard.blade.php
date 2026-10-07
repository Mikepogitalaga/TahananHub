@extends('layouts.tenant')

@section('title', 'Tenant Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Welcome Header -->
    <div class="bg-gradient-to-r from-indigo-700 to-purple-800 text-white rounded-2xl p-6 sm:p-8 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <span class="text-xs uppercase tracking-wider text-indigo-200 font-bold">Tenant Portal</span>
                <h1 class="text-2xl sm:text-3xl font-extrabold mt-1">Welcome, {{ $tenant->first_name }}!</h1>
                <p class="text-xs sm:text-sm text-indigo-100 mt-1">Here is a summary of your room assignment, rental dues, and boarding house notices.</p>
            </div>
            @if($totalBalance > 0)
                <div class="bg-white/10 backdrop-blur-sm px-5 py-3 rounded-xl border border-white/20 text-center sm:text-right">
                    <span class="text-xs text-indigo-200 font-bold uppercase tracking-wider block">Total Balance Due</span>
                    <span class="text-2xl sm:text-3xl font-black text-rose-300">₱{{ number_format($totalBalance, 2) }}</span>
                </div>
            @else
                <div class="bg-white/10 backdrop-blur-sm px-5 py-3 rounded-xl border border-white/20 text-center sm:text-right">
                    <span class="text-xs text-emerald-300 font-bold uppercase tracking-wider block">Rental Dues</span>
                    <span class="text-xl sm:text-2xl font-black text-emerald-300">FULLY PAID ✓</span>
                </div>
            @endif
        </div>
    </div>

    <!-- Top Highlights Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Room Assignment Card -->
        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm space-y-4">
            <div class="flex items-center justify-between">
                <span class="text-xs uppercase font-bold text-slate-400">My Assignment</span>
                <span class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-sm font-bold">🏠</span>
            </div>

            @if($assignment)
                <div>
                    <h3 class="text-lg font-bold text-slate-900">{{ $assignment->property->name }}</h3>
                    <p class="text-xs text-slate-500 mt-0.5">{{ $assignment->property->address }}</p>
                </div>

                <div class="grid grid-cols-2 gap-3 pt-3 border-t border-slate-100 text-sm">
                    <div>
                        <span class="text-xs text-slate-400 block">Room</span>
                        <span class="font-bold text-slate-800">Unit {{ $assignment->room->room_number }}</span>
                    </div>
                    <div>
                        <span class="text-xs text-slate-400 block">Bed</span>
                        <span class="font-bold text-slate-800">Bed {{ $assignment->bed->bed_number }}</span>
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100 text-xs text-slate-600">
                    <span class="font-semibold text-slate-700 block">Landlord / Business:</span>
                    <span>{{ $assignment->property->houseOwner->business_name }}</span>
                </div>
            @else
                <div class="py-6 text-center text-xs text-slate-400">
                    No active room assigned yet.
                </div>
            @endif
        </div>

        <!-- Rent & Dues Status Card -->
        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm space-y-4">
            <div class="flex items-center justify-between">
                <span class="text-xs uppercase font-bold text-slate-400">Payment Status</span>
                <span class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm font-bold">💳</span>
            </div>

            @if($nextDueBilling)
                <div>
                    <span class="text-xs text-slate-400 uppercase font-semibold">Next Payment Due</span>
                    <div class="text-xl font-black text-rose-600 mt-0.5">₱{{ number_format($nextDueBilling->balance, 2) }}</div>
                    <p class="text-xs text-slate-500 mt-1">
                        Due by: <span class="font-bold text-slate-800">{{ \Carbon\Carbon::parse($nextDueBilling->due_date)->format('M d, Y') }}</span>
                    </p>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                    <span class="text-slate-500">Status</span>
                    <span class="px-2.5 py-0.5 rounded-full font-bold uppercase {{ $nextDueBilling->status === 'overdue' ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800' }}">
                        {{ $nextDueBilling->status }}
                    </span>
                </div>

                <div class="pt-2">
                    <a href="{{ route('tenant.billings.show-billing', $nextDueBilling) }}" class="block w-full py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-center font-semibold text-xs rounded-lg transition">
                        View Statement Breakdown &rarr;
                    </a>
                </div>
            @else
                <div>
                    <span class="text-xs text-slate-400 uppercase font-semibold">Monthly Rate</span>
                    <div class="text-xl font-bold text-slate-800 mt-0.5">₱{{ number_format($contract->monthly_rent ?? 0, 2) }}</div>
                    <p class="text-xs text-emerald-600 font-semibold mt-1">✓ No outstanding dues</p>
                </div>
                <div class="pt-6">
                    <a href="{{ route('tenant.billings') }}" class="block w-full py-2 bg-slate-50 hover:bg-slate-100 text-slate-700 text-center font-semibold text-xs rounded-lg transition">
                        View Payment History
                    </a>
                </div>
            @endif
        </div>

        <!-- Quick Actions & Maintenance -->
        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm flex flex-col justify-between space-y-4">
            <div>
                <span class="text-xs uppercase font-bold text-slate-400 block mb-3">Quick Actions</span>
                <div class="space-y-2">
                    <a href="{{ route('tenant.maintenance.create') }}" class="flex items-center justify-between p-3 rounded-xl bg-slate-50 hover:bg-indigo-50 hover:text-indigo-700 border border-slate-200 transition text-sm font-semibold text-slate-800">
                        <span class="flex items-center space-x-2">
                            <span>🔧</span>
                            <span>Report an Issue / Repair</span>
                        </span>
                        <span>&rarr;</span>
                    </a>

                    <a href="{{ route('tenant.contract') }}" class="flex items-center justify-between p-3 rounded-xl bg-slate-50 hover:bg-indigo-50 hover:text-indigo-700 border border-slate-200 transition text-sm font-semibold text-slate-800">
                        <span class="flex items-center space-x-2">
                            <span>📄</span>
                            <span>View Lease Contract</span>
                        </span>
                        <span>&rarr;</span>
                    </a>
                </div>
            </div>

            <div class="p-3 rounded-xl bg-purple-50 border border-purple-100 text-xs text-purple-900">
                <span class="font-bold">Need assistance?</span> Contact your landlord or submit a maintenance ticket anytime.
            </div>
        </div>
    </div>

    <!-- Active Bulletins / Notices -->
    @if($announcements->count() > 0)
        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm space-y-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <span class="text-lg">📢</span>
                    <h2 class="text-base font-bold text-slate-800">Boarding House Bulletins & Notices</h2>
                </div>
                <a href="{{ route('tenant.announcements') }}" class="text-xs text-indigo-600 hover:underline font-semibold">View All</a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($announcements as $notice)
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-2">
                        <div class="flex justify-between items-start">
                            <h3 class="font-bold text-slate-900 text-sm">{{ $notice->title }}</h3>
                            <span class="text-[11px] text-slate-400">{{ $notice->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="text-xs text-slate-600 line-clamp-2 leading-relaxed">{{ $notice->content }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- 2 Column Section: Recent Payments & Open Maintenance Tickets -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Payments -->
        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-base font-bold text-slate-800">My Recent Payments</h2>
                <a href="{{ route('tenant.billings') }}" class="text-xs text-indigo-600 hover:underline font-semibold">All Payments</a>
            </div>

            @if($recentPayments->count() > 0)
                <div class="space-y-3">
                    @foreach($recentPayments as $payment)
                        <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-100 text-sm">
                            <div>
                                <span class="font-mono font-bold text-indigo-600 text-xs block">{{ $payment->payment_number }}</span>
                                <span class="text-xs text-slate-500">{{ \Carbon\Carbon::parse($payment->payment_date)->format('M d, Y') }} &bull; {{ strtoupper($payment->payment_method) }}</span>
                            </div>
                            <div class="text-right">
                                <span class="font-extrabold text-emerald-600 block">₱{{ number_format($payment->amount, 2) }}</span>
                                <a href="{{ route('tenant.billings.show-payment', $payment) }}" class="text-[11px] text-indigo-600 hover:underline font-semibold">Receipt &rarr;</a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="py-6 text-center text-xs text-slate-400">
                    No payment history recorded yet.
                </div>
            @endif
        </div>

        <!-- Open Maintenance Tickets -->
        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-base font-bold text-slate-800">My Maintenance Tickets</h2>
                <a href="{{ route('tenant.maintenance.index') }}" class="text-xs text-indigo-600 hover:underline font-semibold">View All</a>
            </div>

            @if($openMaintenance->count() > 0)
                <div class="space-y-3">
                    @foreach($openMaintenance as $ticket)
                        <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-100 text-sm">
                            <div>
                                <div class="flex items-center space-x-2">
                                    <span class="font-bold text-slate-800 text-xs">{{ $ticket->title }}</span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase
                                        {{ $ticket->status === 'in_progress' ? 'bg-indigo-100 text-indigo-800' : 'bg-amber-100 text-amber-800' }}">
                                        {{ $ticket->status }}
                                    </span>
                                </div>
                                <span class="text-[11px] text-slate-400 mt-0.5 block font-mono">{{ $ticket->request_number }} &bull; {{ $ticket->created_at->diffForHumans() }}</span>
                            </div>
                            <a href="{{ route('tenant.maintenance.show', $ticket) }}" class="px-2.5 py-1 bg-white hover:bg-slate-100 text-slate-700 text-xs font-semibold rounded border border-slate-200 transition">
                                Status &rarr;
                            </a>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="py-6 text-center text-xs text-slate-400">
                    No open maintenance tickets. Everything is running smoothly!
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

