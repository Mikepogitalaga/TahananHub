@extends('layouts.base')

@section('body')
<div class="min-h-screen flex flex-col bg-gray-50">

    {{-- Public Navigation --}}
    <header class="bg-white border-b border-gray-200 sticky top-0 z-30 shadow-sm" x-data="{ open: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 flex items-center justify-between h-16">

            <a href="{{ route('houses.index') }}" class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-indigo-600 flex items-center justify-center text-white font-bold shadow">🏠</div>
                <span class="font-bold text-lg text-gray-900">TahananHub</span>
            </a>

            <nav class="hidden md:flex items-center gap-1 text-sm font-medium">
                <a href="{{ route('houses.index') }}" class="px-3 py-2 rounded-lg {{ request()->routeIs('houses.*') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100' }}">
                    Browse Houses
                </a>
            </nav>

            <div class="hidden md:flex items-center gap-2">
                @auth
                    @if(auth()->user()->isTenant())
                        <a href="{{ route('tenant.dashboard') }}" class="px-3 py-1.5 text-sm font-medium text-indigo-600 hover:text-indigo-800">Dashboard</a>
                        <a href="{{ route('tenant.rental-requests.index') }}" class="px-3 py-1.5 text-sm font-medium text-gray-600 hover:text-gray-900">My Requests</a>
                    @elseif(auth()->user()->isOwner())
                        <a href="{{ route('owner.dashboard') }}" class="px-3 py-1.5 text-sm font-medium text-indigo-600 hover:text-indigo-800">Owner Dashboard</a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button class="px-3 py-1.5 text-xs font-medium text-red-600 bg-red-50 hover:bg-red-100 rounded-lg">Logout</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 border border-gray-300 rounded-lg hover:bg-gray-50">Login</a>
                    <a href="{{ route('register') }}" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg">Register</a>
                @endauth
            </div>

            <button @click="open = !open" class="md:hidden p-2 rounded-lg text-gray-600 hover:bg-gray-100">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
        </div>

        <div x-show="open" x-cloak class="md:hidden border-t border-gray-200 bg-white px-4 py-3 space-y-1 text-sm font-medium">
            <a href="{{ route('houses.index') }}" class="block px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100">Browse Houses</a>
            @auth
                @if(auth()->user()->isTenant())
                    <a href="{{ route('tenant.dashboard') }}" class="block px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100">Dashboard</a>
                    <a href="{{ route('tenant.rental-requests.index') }}" class="block px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100">My Requests</a>
                @elseif(auth()->user()->isOwner())
                    <a href="{{ route('owner.dashboard') }}" class="block px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100">Owner Dashboard</a>
                @endif
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="w-full text-left px-3 py-2 rounded-lg text-red-600 hover:bg-red-50">Logout</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="block px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100">Login</a>
                <a href="{{ route('register') }}" class="block px-3 py-2 rounded-lg text-indigo-700 hover:bg-indigo-50">Register</a>
            @endauth
        </div>
    </header>

    <main class="flex-1">
        @if(session('success'))
            <div class="max-w-7xl mx-auto px-4 sm:px-6 pt-4">
                <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center gap-2">
                    <span>✓</span><span>{{ session('success') }}</span>
                </div>
            </div>
        @endif
        @if(session('error'))
            <div class="max-w-7xl mx-auto px-4 sm:px-6 pt-4">
                <div class="p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-sm flex items-center gap-2">
                    <span>⚠️</span><span>{{ session('error') }}</span>
                </div>
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="bg-white border-t border-gray-200 mt-12 py-8 text-center text-sm text-gray-500">
        © {{ date('Y') }} TahananHub — Find Your Next Home
    </footer>
</div>
@endsection
