@extends('layouts.base')

@section('body')
<div class="min-h-screen bg-gray-50" x-data>

    {{-- Desktop Sidebar --}}
    <x-tenant.sidebar />

    {{-- More Menu Drawer (mobile + tablet) --}}
    <x-tenant.more-menu />

    {{-- Fixed Top Header --}}
    <x-tenant.header />

    {{-- Main wrapper: offset for sidebar on desktop, header on all --}}
    <div class="lg:pl-60 xl:pl-64 pt-14">

        {{-- Page content --}}
        <main class="min-h-[calc(100vh-3.5rem)] px-4 sm:px-6 lg:px-8 py-5 sm:py-7
                     pb-24 lg:pb-8">

            {{-- Flash messages --}}
            @if(session('success'))
                <div class="mb-5 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center gap-2 shadow-sm">
                    <svg class="w-4 h-4 text-emerald-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif
            @if(session('error'))
                <div class="mb-5 p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-sm flex items-center gap-2 shadow-sm">
                    <svg class="w-4 h-4 text-red-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    {{-- Mobile Bottom Navigation --}}
    <x-tenant.bottom-navigation />

</div>
@endsection
