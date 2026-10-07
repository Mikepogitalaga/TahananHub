@extends('layouts.base')

@section('body')
<div class="min-h-screen flex" x-data="{ sidebarOpen: false }">

    {{-- Sidebar for Desktop & Mobile --}}
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
           class="fixed inset-y-0 left-0 z-40 w-64 bg-slate-900 text-slate-100 transition-transform duration-200 ease-in-out flex flex-col">
        
        {{-- Brand / House Owner Business --}}
        <div class="h-16 flex items-center justify-between px-6 border-b border-slate-800">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-emerald-600 flex items-center justify-center font-bold text-white shadow">
                    🏠
                </div>
                <div class="overflow-hidden">
                    <span class="font-bold text-sm tracking-wide text-white block truncate">
                        {{ auth()->user()->houseOwner?->business_name ?? 'My Boarding House' }}
                    </span>
                    <span class="block text-[10px] text-emerald-400 font-medium tracking-wider uppercase">House Owner</span>
                </div>
            </div>
            <button @click="sidebarOpen = false" class="lg:hidden text-slate-400 hover:text-white">✕</button>
        </div>

        {{-- Nav Links --}}
        <nav class="flex-1 overflow-y-auto px-4 py-4 space-y-1 text-sm font-medium">
            <a href="{{ route('owner.dashboard') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('owner.dashboard') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <span>📊</span> Dashboard
            </a>

            <div class="pt-3 pb-1 px-3 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Properties & Units</div>

            <a href="{{ route('owner.properties.index') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('owner.properties.*') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <span>🏢</span> Properties
            </a>

            <a href="{{ route('owner.rooms.index') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('owner.rooms.*') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <span>🚪</span> Rooms & Beds
            </a>

            <div class="pt-3 pb-1 px-3 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Tenants & Leases</div>

            <a href="{{ route('owner.tenants.index') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('owner.tenants.*') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <span>👥</span> Tenants
            </a>

            <a href="{{ route('owner.contracts.index') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('owner.contracts.*') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <span>📝</span> Rental Contracts
            </a>

            <div class="pt-3 pb-1 px-3 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Financials</div>

            <a href="{{ route('owner.billings.index') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('owner.billings.*') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <span>🧾</span> Rent Billing
            </a>

            <a href="{{ route('owner.payments.index') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('owner.payments.*') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <span>💰</span> Payments
            </a>

            <a href="{{ route('owner.expenses.index') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('owner.expenses.*') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <span>📉</span> Expenses
            </a>

            <div class="pt-3 pb-1 px-3 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Operations</div>

            <a href="{{ route('owner.maintenance.index') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('owner.maintenance.*') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <span>🔧</span> Maintenance
            </a>

            <a href="{{ route('owner.announcements.index') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('owner.announcements.*') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <span>📢</span> Announcements
            </a>

            <a href="{{ route('owner.reports.index') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('owner.reports.*') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <span>📈</span> Reports
            </a>

            <div class="pt-3 pb-1 px-3 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Privacy & Security</div>

            <a href="{{ route('owner.privacy.index') }}"
               class="flex items-center justify-between px-3 py-2 rounded-lg {{ request()->routeIs('owner.privacy.*') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <span class="flex items-center gap-3">
                    <span>🛡️</span> Privacy & Access
                </span>
                @php
                    $pendingReq = auth()->user()->houseOwner ? \App\Models\OwnerAccessRequest::where('house_owner_id', auth()->user()->houseOwner->id)->where('status', 'pending')->count() : 0;
                @endphp
                @if($pendingReq > 0)
                    <span class="text-xs px-2 py-0.5 rounded-full bg-amber-400 text-slate-900 font-bold animate-pulse">{{ $pendingReq }}</span>
                @endif
            </a>
        </nav>

        {{-- Current User & Logout --}}
        <div class="p-4 border-t border-slate-800 flex items-center justify-between">
            <div class="flex items-center gap-2 overflow-hidden">
                <div class="w-8 h-8 rounded-full bg-emerald-700 flex items-center justify-center font-bold text-xs text-white">
                    {{ substr(auth()->user()->name, 0, 2) }}
                </div>
                <div class="truncate text-xs">
                    <p class="font-medium text-white truncate">{{ auth()->user()->name }}</p>
                    <p class="text-slate-400 text-[10px] truncate">{{ auth()->user()->email }}</p>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" title="Logout" class="text-slate-400 hover:text-red-400 p-1.5 rounded-lg hover:bg-slate-800">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                </button>
            </form>
        </div>
    </aside>

    {{-- Main Content Area --}}
    <div class="flex-1 lg:pl-64 flex flex-col min-h-screen">
        {{-- Topbar --}}
        <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-6 sticky top-0 z-30 shadow-sm">
            <div class="flex items-center gap-3">
                <button @click="sidebarOpen = true" class="lg:hidden text-gray-600 hover:text-gray-900 p-2 rounded-lg hover:bg-gray-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <h1 class="text-lg font-bold text-gray-800">@yield('page_title', 'Owner Workspace')</h1>
            </div>

            <div class="flex items-center gap-3">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    Private Workspace
                </span>
            </div>
        </header>

        {{-- Page Content --}}
        <main class="flex-1 p-6 max-w-7xl w-full mx-auto">
            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="mb-5 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center justify-between shadow-sm">
                    <div class="flex items-center gap-2">
                        <span>✓</span>
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-5 p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-sm flex items-center justify-between shadow-sm">
                    <div class="flex items-center gap-2">
                        <span>⚠️</span>
                        <span>{{ session('error') }}</span>
                    </div>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>
@endsection

