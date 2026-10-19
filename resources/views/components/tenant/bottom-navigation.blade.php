@php
$bottomNav = [
    ['route' => 'tenant.dashboard', 'label' => 'Home',    'match' => 'tenant.dashboard', 'icon' => 'home'],
    ['route' => 'houses.index',     'label' => 'Browse',  'match' => 'houses.*',          'icon' => 'browse'],
    ['route' => 'tenant.contract',  'label' => 'My Room', 'match' => 'tenant.contract*',  'icon' => 'room'],
    ['route' => 'tenant.billings',  'label' => 'Billing', 'match' => 'tenant.billings*',  'icon' => 'billing'],
];
@endphp

<nav class="lg:hidden fixed bottom-0 left-0 right-0 z-30 bg-white border-t border-gray-100 safe-area-bottom"
     style="padding-bottom: env(safe-area-inset-bottom);">
    <div class="flex items-stretch h-16">

        @foreach($bottomNav as $item)
            @php $active = request()->routeIs($item['match']); @endphp
            <a href="{{ route($item['route']) }}"
               class="flex-1 flex flex-col items-center justify-center gap-0.5 transition
                      {{ $active ? 'text-red-600' : 'text-gray-400 hover:text-gray-600' }}">
                <span class="w-6 h-6 flex items-center justify-center">
                    @if($item['icon'] === 'home')
                        <svg class="w-5 h-5" fill="{{ $active ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    @elseif($item['icon'] === 'browse')
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    @elseif($item['icon'] === 'room')
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    @elseif($item['icon'] === 'billing')
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
                    @endif
                </span>
                <span class="text-[10px] font-semibold leading-none">{{ $item['label'] }}</span>
                @if($active)
                    <span class="absolute bottom-0 w-8 h-0.5 bg-red-500 rounded-t-full" style="position:static; display:block; width:2rem; height:2px; background:#ef4444; border-radius:9999px 9999px 0 0; margin-top:1px;"></span>
                @endif
            </a>
        @endforeach

        {{-- More button --}}
        @php
            $moreActive = request()->routeIs('tenant.rental-requests.*')
                       || request()->routeIs('tenant.maintenance.*')
                       || request()->routeIs('tenant.announcements')
                       || request()->routeIs('tenant.profile');
        @endphp
        <button @click="$dispatch('open-more-menu')"
                class="flex-1 flex flex-col items-center justify-center gap-0.5 transition
                       {{ $moreActive ? 'text-red-600' : 'text-gray-400 hover:text-gray-600' }}"
                aria-label="More options">
            <span class="w-6 h-6 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>
                </svg>
            </span>
            <span class="text-[10px] font-semibold leading-none">More</span>
        </button>
    </div>
</nav>
