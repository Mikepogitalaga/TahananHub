@extends('layouts.base')

@section('body')
<div class="min-h-screen flex flex-col bg-gray-50">

    {{-- Top Navigation for Tenants --}}
    <header class="bg-white border-b border-gray-200 sticky top-0 z-30 shadow-sm" x-data="{ mobileMenuOpen: false }">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 flex items-center justify-between h-16">
            
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-indigo-600 flex items-center justify-center font-bold text-white shadow">
                    🛏️
                </div>
                <div>
                    <span class="font-bold text-base text-gray-900">E-Boarding</span>
                    <span class="block text-[10px] text-indigo-600 font-medium tracking-wider uppercase">Tenant Portal</span>
                </div>
            </div>

            {{-- Desktop Nav --}}
            <nav class="hidden md:flex items-center gap-1 text-sm font-medium">
                <a href="{{ route('tenant.dashboard') }}"
                   class="px-3 py-2 rounded-lg {{ request()->routeIs('tenant.dashboard') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100' }}">
                    Dashboard
                </a>
                <a href="{{ route('tenant.contract') }}"
                   class="px-3 py-2 rounded-lg {{ request()->routeIs('tenant.contract') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100' }}">
                    My Room
                </a>
                <a href="{{ route('tenant.billings') }}"
                   class="px-3 py-2 rounded-lg {{ request()->routeIs('tenant.billings') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100' }}">
                    Rent & Billing
                </a>
                <a href="{{ route('tenant.maintenance.index') }}"
                   class="px-3 py-2 rounded-lg {{ request()->routeIs('tenant.maintenance.*') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100' }}">
                    Maintenance
                </a>
                <a href="{{ route('tenant.announcements') }}"
                   class="px-3 py-2 rounded-lg {{ request()->routeIs('tenant.announcements') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100' }}">
                    Announcements
                </a>
                <a href="{{ route('tenant.profile') }}"
                   class="px-3 py-2 rounded-lg {{ request()->routeIs('tenant.profile') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100' }}">
                    Profile
                </a>
            </nav>

            {{-- User Dropdown / Logout --}}
            <div class="flex items-center gap-3">
                <div class="hidden sm:block text-right">
                    <p class="text-xs font-semibold text-gray-800">{{ auth()->user()->name }}</p>
                    <p class="text-[10px] text-gray-500">{{ auth()->user()->email }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="px-3 py-1.5 text-xs font-medium text-red-600 bg-red-50 hover:bg-red-100 rounded-lg transition">
                        Logout
                    </button>
                </form>

                {{-- Mobile menu button --}}
                <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden p-2 rounded-lg text-gray-600 hover:bg-gray-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
            </div>
        </div>

        {{-- Mobile dropdown menu --}}
        <div x-show="mobileMenuOpen" x-cloak class="md:hidden border-t border-gray-200 bg-white px-4 py-3 space-y-1 text-sm font-medium">
            <a href="{{ route('tenant.dashboard') }}" class="block px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100">Dashboard</a>
            <a href="{{ route('tenant.contract') }}" class="block px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100">My Room</a>
            <a href="{{ route('tenant.billings') }}" class="block px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100">Rent & Billing</a>
            <a href="{{ route('tenant.maintenance.index') }}" class="block px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100">Maintenance</a>
            <a href="{{ route('tenant.announcements') }}" class="block px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100">Announcements</a>
            <a href="{{ route('tenant.profile') }}" class="block px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100">Profile</a>
        </div>
    </header>

    {{-- Main Content Area --}}
    <main class="flex-1 max-w-6xl w-full mx-auto p-4 sm:p-6">
        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="mb-5 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center gap-2 shadow-sm">
                <span>✓</span>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-5 p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-sm flex items-center gap-2 shadow-sm">
                <span>⚠️</span>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @yield('content')
    </main>
</div>
@endsection

