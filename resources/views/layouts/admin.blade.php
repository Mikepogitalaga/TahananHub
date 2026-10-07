@extends('layouts.base')

@section('body')
<div class="min-h-screen flex" x-data="{ sidebarOpen: false }">

    {{-- Sidebar for Desktop & Mobile --}}
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
           class="fixed inset-y-0 left-0 z-40 w-64 bg-slate-900 text-slate-100 transition-transform duration-200 ease-in-out flex flex-col">
        
        {{-- Brand --}}
        <div class="h-16 flex items-center justify-between px-6 border-b border-slate-800">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-blue-600 flex items-center justify-center font-bold text-white shadow">
                    EB
                </div>
                <div>
                    <span class="font-bold text-base tracking-wide text-white">E-Boarding</span>
                    <span class="block text-[10px] text-blue-400 font-medium tracking-wider uppercase">Super Admin</span>
                </div>
            </div>
            <button @click="sidebarOpen = false" class="lg:hidden text-slate-400 hover:text-white">
                ✕
            </button>
        </div>

        {{-- Nav Links --}}
        <nav class="flex-1 overflow-y-auto px-4 py-4 space-y-1 text-sm font-medium">
            <a href="{{ route('admin.dashboard') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.dashboard') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <span>📊</span> Dashboard
            </a>

            <div class="pt-4 pb-1 px-3 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Platform Management</div>

            <a href="{{ route('admin.users.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.users.*') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <span>👥</span> All Accounts
            </a>

            <a href="{{ route('admin.owners.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.owners.*') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <span>🏢</span> House Owners
            </a>

            <div class="pt-4 pb-1 px-3 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Privacy & Security</div>

            <a href="{{ route('admin.access-requests.index') }}"
               class="flex items-center justify-between px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.access-requests.*') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <span class="flex items-center gap-3">
                    <span>🛡️</span> Access Requests
                </span>
                @php
                    $pendingCount = \App\Models\OwnerAccessRequest::where('status', 'pending')->count();
                @endphp
                @if($pendingCount > 0)
                    <span class="text-xs px-2 py-0.5 rounded-full bg-amber-500 text-slate-950 font-bold">{{ $pendingCount }}</span>
                @endif
            </a>

            <a href="{{ route('admin.activity-logs.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.activity-logs.*') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <span>📜</span> Activity Logs
            </a>

            <a href="{{ route('admin.backups.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.backups.*') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <span>💾</span> Database Backups
            </a>

            <div class="pt-4 pb-1 px-3 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">System</div>

            <a href="{{ route('admin.settings.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.settings.*') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <span>⚙️</span> Platform Settings
            </a>
        </nav>

        {{-- Privacy Notice in Sidebar --}}
        <div class="p-4 mx-3 mb-3 bg-slate-800/80 rounded-xl border border-slate-700/60 text-xs text-slate-300">
            <div class="font-semibold text-amber-400 flex items-center gap-1.5 mb-1">
                <span>🔒</span> Privacy Enforced
            </div>
            <p class="text-[11px] leading-tight text-slate-400">
                Owner tenants, finances & private docs require explicit owner permission.
            </p>
        </div>

        {{-- Current User & Logout --}}
        <div class="p-4 border-t border-slate-800 flex items-center justify-between">
            <div class="flex items-center gap-2 overflow-hidden">
                <div class="w-8 h-8 rounded-full bg-slate-700 flex items-center justify-center font-bold text-xs text-slate-200">
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
                <h1 class="text-lg font-bold text-gray-800">@yield('page_title', 'Super Admin')</h1>
            </div>

            <div class="flex items-center gap-3">
                <span class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    Platform Healthy
                </span>
                <span class="text-xs text-gray-500 font-mono">{{ date('M d, Y') }}</span>
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

