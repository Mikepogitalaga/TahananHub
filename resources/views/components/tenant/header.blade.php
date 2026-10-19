@php
    $unreadCount = auth()->user()->unreadNotifications()->count();
@endphp

<header class="fixed top-0 left-0 right-0 lg:left-60 xl:left-64 z-30 h-14 bg-white border-b border-gray-100 flex items-center px-4 gap-3"
        x-data="{ notifOpen: false }"
        @keydown.escape.window="notifOpen = false">

    {{-- Mobile: Hamburger --}}
    <button @click="$dispatch('open-more-menu')"
            class="lg:hidden p-2 -ml-1 rounded-xl text-gray-500 hover:bg-gray-100 transition"
            aria-label="Open menu">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
    </button>

    {{-- Mobile: Brand --}}
    <a href="{{ route('tenant.dashboard') }}" class="lg:hidden flex items-center gap-2 flex-shrink-0">
        <div class="w-7 h-7 rounded-lg bg-red-600 flex items-center justify-center flex-shrink-0">
            <svg class="w-3.5 h-3.5 text-white" fill="currentColor" viewBox="0 0 20 20"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h4a1 1 0 001-1v-3h2v3a1 1 0 001 1h4a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/></svg>
        </div>
        <span class="font-extrabold text-sm text-gray-900">TahananHub</span>
    </a>

    {{-- Desktop: Page title slot --}}
    <div class="hidden lg:block flex-1 min-w-0">
        <h1 class="text-sm font-semibold text-gray-700 truncate">@yield('page-title', 'Tenant Portal')</h1>
    </div>

    <div class="flex-1 lg:flex-none"></div>

    {{-- Notification Bell --}}
    <div class="relative flex-shrink-0">
        <button @click="notifOpen = !notifOpen"
                class="relative p-2 rounded-xl text-gray-500 hover:bg-gray-100 transition"
                aria-label="Notifications">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
            @if($unreadCount > 0)
                <span class="absolute top-1 right-1 w-4 h-4 bg-red-500 text-white text-[9px] font-bold rounded-full flex items-center justify-center leading-none">
                    {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                </span>
            @endif
        </button>

        {{-- Notification Dropdown --}}
        <div x-show="notifOpen" x-cloak
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             @click.outside="notifOpen = false"
             class="absolute right-0 top-full mt-2 w-80 bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden z-50">

            <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
                <span class="text-sm font-bold text-gray-900">Notifications</span>
                @if($unreadCount > 0)
                    <span class="text-xs text-red-600 font-semibold">{{ $unreadCount }} unread</span>
                @endif
            </div>

            <div class="max-h-72 overflow-y-auto divide-y divide-gray-50">
                @forelse(auth()->user()->notifications()->latest()->take(8)->get() as $notif)
                    @php $data = $notif->data; @endphp
                    <a href="{{ $data['action_url'] ?? '#' }}"
                       class="flex items-start gap-3 px-4 py-3 hover:bg-gray-50 transition {{ $notif->read_at ? 'opacity-60' : '' }}">
                        <div class="w-2 h-2 rounded-full mt-1.5 flex-shrink-0 {{ $notif->read_at ? 'bg-gray-200' : 'bg-red-500' }}"></div>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs text-gray-800 leading-snug">{{ $data['message'] ?? 'New notification' }}</p>
                            <p class="text-[10px] text-gray-400 mt-0.5">{{ $notif->created_at->diffForHumans() }}</p>
                        </div>
                    </a>
                @empty
                    <div class="px-4 py-8 text-center">
                        <svg class="w-8 h-8 text-gray-200 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        <p class="text-xs text-gray-400">No notifications yet</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Avatar / Profile --}}
    <a href="{{ route('tenant.profile') }}"
       class="flex-shrink-0 w-8 h-8 rounded-full bg-red-100 text-red-700 flex items-center justify-center text-xs font-bold hover:ring-2 hover:ring-red-300 transition"
       aria-label="My profile">
        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
    </a>
</header>
