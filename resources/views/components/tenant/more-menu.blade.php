@php
$moreLinks = [
    ['route' => 'tenant.rental-requests.index', 'label' => 'My Requests',  'match' => 'tenant.rental-requests.*', 'icon' => 'requests', 'desc' => 'Track rental applications'],
    ['route' => 'tenant.maintenance.index',     'label' => 'Maintenance',  'match' => 'tenant.maintenance.*',     'icon' => 'wrench',   'desc' => 'Report & track repairs'],
    ['route' => 'tenant.announcements',         'label' => 'Notices',      'match' => 'tenant.announcements',     'icon' => 'megaphone','desc' => 'Landlord announcements'],
    ['route' => 'tenant.profile',               'label' => 'My Profile',   'match' => 'tenant.profile',           'icon' => 'user',     'desc' => 'Account & password'],
];
@endphp

<div x-data="{ open: false }"
     @open-more-menu.window="open = true"
     @keydown.escape.window="open = false">

    {{-- Backdrop --}}
    <div x-show="open" x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="open = false"
         class="fixed inset-0 bg-black/40 z-40 lg:hidden"
         aria-hidden="true">
    </div>

    {{-- Drawer --}}
    <div x-show="open" x-cloak
         x-transition:enter="transition ease-out duration-250"
         x-transition:enter-start="translate-y-full"
         x-transition:enter-end="translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="translate-y-0"
         x-transition:leave-end="translate-y-full"
         class="fixed bottom-0 left-0 right-0 z-50 lg:hidden bg-white rounded-t-2xl shadow-2xl"
         style="padding-bottom: env(safe-area-inset-bottom);"
         role="dialog" aria-modal="true" aria-label="More menu">

        {{-- Handle --}}
        <div class="flex justify-center pt-3 pb-1">
            <div class="w-10 h-1 bg-gray-200 rounded-full"></div>
        </div>

        {{-- User strip --}}
        <div class="flex items-center gap-3 px-5 py-3 border-b border-gray-100">
            <div class="w-10 h-10 rounded-full bg-red-100 text-red-700 flex items-center justify-center text-sm font-bold flex-shrink-0">
                {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-sm font-bold text-gray-900 truncate">{{ auth()->user()->name }}</p>
                <p class="text-xs text-gray-400 truncate">{{ auth()->user()->email }}</p>
            </div>
            <button @click="open = false"
                    class="p-2 rounded-xl text-gray-400 hover:bg-gray-100 transition flex-shrink-0"
                    aria-label="Close menu">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Links --}}
        <div class="px-4 py-3 grid grid-cols-2 gap-2">
            @foreach($moreLinks as $item)
                @php $active = request()->routeIs($item['match']); @endphp
                <a href="{{ route($item['route']) }}" @click="open = false"
                   class="flex items-center gap-3 p-3 rounded-xl border transition
                          {{ $active ? 'bg-red-50 border-red-200 text-red-700' : 'bg-gray-50 border-gray-100 text-gray-700 hover:bg-gray-100' }}">
                    <span class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0
                                 {{ $active ? 'bg-red-100 text-red-600' : 'bg-white text-gray-500 border border-gray-200' }}">
                        @if($item['icon'] === 'requests')
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        @elseif($item['icon'] === 'wrench')
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        @elseif($item['icon'] === 'megaphone')
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                        @elseif($item['icon'] === 'user')
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        @endif
                    </span>
                    <div class="min-w-0">
                        <p class="text-xs font-semibold leading-tight">{{ $item['label'] }}</p>
                        <p class="text-[10px] text-gray-400 leading-tight mt-0.5">{{ $item['desc'] }}</p>
                    </div>
                </a>
            @endforeach
        </div>

        {{-- Logout --}}
        <div class="px-4 pb-4">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                        class="w-full flex items-center justify-center gap-2 py-3 rounded-xl bg-red-50 hover:bg-red-100 text-red-600 text-sm font-semibold transition border border-red-100">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    Sign Out
                </button>
            </form>
        </div>
    </div>

    {{-- Desktop sidebar drawer (md only) --}}
    <div x-show="open" x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-x-4"
         x-transition:enter-end="opacity-100 translate-x-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-x-0"
         x-transition:leave-end="opacity-0 -translate-x-4"
         @click.outside="open = false"
         class="hidden md:block lg:hidden fixed top-14 left-0 bottom-0 w-72 bg-white border-r border-gray-100 shadow-xl z-40 overflow-y-auto">

        <div class="flex items-center gap-3 px-5 py-4 border-b border-gray-100">
            <div class="w-10 h-10 rounded-full bg-red-100 text-red-700 flex items-center justify-center text-sm font-bold">
                {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-sm font-bold text-gray-900 truncate">{{ auth()->user()->name }}</p>
                <p class="text-xs text-gray-400 truncate">{{ auth()->user()->email }}</p>
            </div>
        </div>

        @php
        $allNav = [
            ['route' => 'tenant.dashboard',             'label' => 'Dashboard',   'match' => 'tenant.dashboard'],
            ['route' => 'houses.index',                 'label' => 'Browse',      'match' => 'houses.*'],
            ['route' => 'tenant.contract',              'label' => 'My Room',     'match' => 'tenant.contract*'],
            ['route' => 'tenant.billings',              'label' => 'Billing',     'match' => 'tenant.billings*'],
            ['route' => 'tenant.rental-requests.index', 'label' => 'My Requests', 'match' => 'tenant.rental-requests.*'],
            ['route' => 'tenant.maintenance.index',     'label' => 'Maintenance', 'match' => 'tenant.maintenance.*'],
            ['route' => 'tenant.announcements',         'label' => 'Notices',     'match' => 'tenant.announcements'],
            ['route' => 'tenant.profile',               'label' => 'My Profile',  'match' => 'tenant.profile'],
        ];
        @endphp
        <nav class="px-3 py-3 space-y-0.5">
            @foreach($allNav as $item)
                @php $active = request()->routeIs($item['match']); @endphp
                <a href="{{ route($item['route']) }}" @click="open = false"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition
                          {{ $active ? 'bg-red-50 text-red-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                    {{ $item['label'] }}
                </a>
            @endforeach
        </nav>

        <div class="px-4 py-3 border-t border-gray-100">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full py-2.5 rounded-xl bg-red-50 hover:bg-red-100 text-red-600 text-sm font-semibold transition">
                    Sign Out
                </button>
            </form>
        </div>
    </div>
</div>
