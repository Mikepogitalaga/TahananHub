@extends('layouts.tenant')

@section('title', 'Agreement ' . $contract->contract_number)

@section('content')
<div class="space-y-6 max-w-4xl mx-auto">
    <div class="flex items-center justify-between print:hidden">
        <a href="{{ route('tenant.contract') }}" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-lg transition flex items-center space-x-1">
            <span>&larr; Back to Contracts</span>
        </a>

        <button onclick="window.print()" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white text-sm font-semibold rounded-lg shadow transition flex items-center space-x-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            <span>Print Agreement</span>
        </button>
    </div>

    <!-- Printable Contract Sheet -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-8 print:border-none print:shadow-none print:p-0 space-y-6">
        <div class="text-center border-b border-slate-200 pb-6">
            <span class="text-xs uppercase font-extrabold tracking-widest text-indigo-700">Official Boarding House Rental Agreement</span>
            <h1 class="text-2xl font-black text-slate-900 mt-1">{{ $contract->property->houseOwner->business_name }}</h1>
            <p class="text-xs text-slate-500 mt-1">{{ $contract->property->address }}</p>
            <div class="mt-3 inline-block font-mono text-xs px-3 py-1 bg-slate-100 rounded-full text-slate-700">
                Contract Reference: <strong>{{ $contract->contract_number }}</strong>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-6 text-sm">
            <div>
                <span class="text-xs uppercase font-bold text-slate-400 block mb-1">Tenant Details</span>
                <div class="font-bold text-slate-900">{{ $tenant->full_name }}</div>
                <div class="text-xs text-slate-600 mt-0.5">Phone: {{ $tenant->contact_number ?? 'N/A' }}</div>
                <div class="text-xs text-slate-600">Email: {{ $tenant->email ?? 'N/A' }}</div>
                <div class="text-xs text-slate-600">Emergency Contact: {{ $tenant->emergency_contact }} ({{ $tenant->emergency_contact_number }})</div>
            </div>

            <div>
                <span class="text-xs uppercase font-bold text-slate-400 block mb-1">Rental Assignment</span>
                <div class="font-bold text-slate-900">{{ $contract->property->name }}</div>
                <div class="text-xs text-slate-600 mt-0.5">Room: {{ $contract->room?->room_number }} &bull; Bed: {{ $contract->bed?->bed_number }}</div>
                <div class="text-xs text-slate-600">Type: {{ ucfirst($contract->room?->room_type ?? 'Bedspace') }}</div>
            </div>
        </div>

        <!-- Financial Terms -->
        <div class="bg-slate-50 border border-slate-200 rounded-xl p-5 grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
            <div>
                <span class="text-xs text-slate-400 block font-semibold">Monthly Rent</span>
                <span class="text-xl font-bold text-indigo-700">₱{{ number_format($contract->monthly_rent, 2) }}</span>
            </div>
            <div>
                <span class="text-xs text-slate-400 block font-semibold">Security Deposit</span>
                <span class="text-base font-semibold text-slate-800">₱{{ number_format($contract->security_deposit, 2) }}</span>
            </div>
            <div>
                <span class="text-xs text-slate-400 block font-semibold">Monthly Due Date</span>
                <span class="text-base font-semibold text-slate-800">Every {{ $contract->due_day }}th</span>
            </div>
            <div>
                <span class="text-xs text-slate-400 block font-semibold">Late Fee Penalty</span>
                <span class="text-base font-semibold text-rose-600">₱{{ number_format($contract->late_fee, 2) }}</span>
            </div>
        </div>

        <!-- Terms & Conditions Text -->
        <div class="space-y-3 text-sm text-slate-700">
            <h3 class="font-bold text-slate-900 uppercase text-xs tracking-wider">Terms & Stipulations</h3>
            <div class="p-4 bg-slate-50 rounded-xl border border-slate-200 text-xs text-slate-700 whitespace-pre-line leading-relaxed">
                {{ $contract->terms ?: "1. RENT PAYMENT: Rent is payable on or before the designated due date each month. Failure to pay on time may incur late penalty fees.\n2. SECURITY DEPOSIT: Security deposit covers outstanding utility charges or damages upon move-out and is refundable subject to property inspection.\n3. PREMISES USE & HOUSE RULES: The tenant agrees to observe house quiet hours, cleanliness in shared areas, and adhere to visitor policies.\n4. TERMINATION: Either party must provide 30 days written advance notice prior to vacating." }}
            </div>
        </div>

        <!-- Signature Lines -->
        <div class="pt-8 grid grid-cols-2 gap-10 text-center text-xs">
            <div>
                <div class="border-b border-slate-300 pb-10"></div>
                <div class="mt-2 font-bold text-slate-800">{{ $tenant->full_name }}</div>
                <div class="text-slate-400">Tenant Signature</div>
            </div>
            <div>
                <div class="border-b border-slate-300 pb-10"></div>
                <div class="mt-2 font-bold text-slate-800">{{ $contract->property->houseOwner->business_name }}</div>
                <div class="text-slate-400">Authorized Landlord / Manager</div>
            </div>
        </div>
    </div>
</div>
@endsection

