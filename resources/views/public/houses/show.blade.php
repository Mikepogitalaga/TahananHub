@extends('layouts.public')

@section('title', $property->name . ' — TahananHub')

@section('content')

{{-- On mobile: no horizontal padding so cards go edge-to-edge --}}
<div class="max-w-7xl mx-auto sm:px-6 py-0 sm:py-8">

    {{-- Breadcrumb — has its own padding on mobile --}}
    <nav class="text-xs sm:text-sm text-gray-500 mb-0 sm:mb-6 flex items-center gap-1.5 px-4 sm:px-0 py-3 sm:py-0">
        <a href="{{ route('houses.index') }}" class="hover:text-indigo-600 flex items-center gap-1">
            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Browse
        </a>
        <span class="text-gray-300">/</span>
        <span class="text-gray-800 font-medium truncate">{{ $property->name }}</span>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-0 sm:gap-8">

        {{-- Left: Gallery + Details --}}
        <div class="lg:col-span-2 space-y-0 sm:space-y-6">

            {{-- Gallery — full bleed on mobile --}}
            @php $photos = $property->photos; @endphp
            <div class="sm:rounded-2xl overflow-hidden bg-gray-100 relative group"
                 x-data="{
                     active: 0,
                     timer: null,
                     startX: 0,
                     endX: 0,
                     get total() { return {{ $photos->count() }}; },
                     next() { this.active = (this.active + 1) % this.total; },
                     prev() { this.active = (this.active - 1 + this.total) % this.total; },
                     startAuto() {
                         this.stopAuto();
                         this.timer = setInterval(() => this.next(), 4000);
                     },
                     stopAuto() { if (this.timer) { clearInterval(this.timer); this.timer = null; } },
                     init() {
                         this.startAuto();
                         this.$el.addEventListener('mouseenter', () => this.stopAuto());
                         this.$el.addEventListener('mouseleave', () => this.startAuto());
                         this.$el.addEventListener('touchstart', (e) => { this.startX = e.changedTouches[0].screenX; this.stopAuto(); }, { passive: true });
                         this.$el.addEventListener('touchend', (e) => {
                             this.endX = e.changedTouches[0].screenX;
                             const diff = this.startX - this.endX;
                             if (Math.abs(diff) > 50) { diff > 0 ? this.next() : this.prev(); }
                             this.startAuto();
                         }, { passive: true });
                     }
                 }"
                 x-init="init()">
                @if($photos->isNotEmpty())
                    <div class="relative h-56 sm:h-96">
                        @foreach($photos as $i => $photo)
                            <img src="{{ asset('storage/' . $photo->path) }}" alt="{{ $photo->caption ?? $property->name }}"
                                 x-show="active === {{ $i }}" x-transition.opacity.duration.300ms
                                 class="absolute inset-0 w-full h-full object-cover">
                        @endforeach
                        @if($photos->count() > 1)
                            <button @click="prev()" class="absolute left-2 sm:left-4 top-1/2 -translate-y-1/2 p-2 bg-black/40 hover:bg-black/60 backdrop-blur-sm rounded-full text-white transition opacity-0 group-hover:opacity-100">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                            </button>
                            <button @click="next()" class="absolute right-2 sm:right-4 top-1/2 -translate-y-1/2 p-2 bg-black/40 hover:bg-black/60 backdrop-blur-sm rounded-full text-white transition opacity-0 group-hover:opacity-100">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </button>
                        @endif
                    </div>
                    @if($photos->count() > 1)
                        <div class="absolute bottom-3 left-1/2 -translate-x-1/2 flex items-center gap-1.5 bg-black/50 backdrop-blur-sm px-2.5 py-1.5 rounded-full">
                            <button @click="prev()" class="p-0.5 hover:bg-white/20 rounded-full transition">
                                <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                            </button>
                            <span class="text-white text-[10px] font-medium px-1" x-text="active + 1 + ' / ' + total"></span>
                            <button @click="next()" class="p-0.5 hover:bg-white/20 rounded-full transition">
                                <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </button>
                        </div>
                    @endif
                @else
                    <div class="h-56 sm:h-96 flex items-center justify-center bg-gradient-to-br from-indigo-100 to-indigo-200">
                        <span class="text-6xl sm:text-9xl">🏠</span>
                    </div>
                @endif
            </div>

            {{-- Thumbnail strip --}}
            @if($photos->count() > 1)
                <div class="flex gap-1.5 overflow-x-auto pb-1 px-4 sm:px-0">
                    @foreach($photos as $i => $photo)
                        <button @click="active = {{ $i }}"
                                class="flex-shrink-0 w-16 h-12 sm:w-20 sm:h-16 rounded-lg overflow-hidden border-2 transition-all duration-200"
                                :class="active === {{ $i }} ? 'border-indigo-600 ring-2 ring-indigo-200' : 'border-transparent hover:border-gray-300'">
                            <img src="{{ asset('storage/' . $photo->path) }}" alt="" class="w-full h-full object-cover">
                        </button>
                    @endforeach
                </div>
            @endif

            {{-- Property Info — no rounded corners / border on mobile, full bleed --}}
            <div class="bg-white sm:rounded-2xl sm:border sm:border-gray-200 px-4 py-5 sm:p-8">

                {{-- Title + badge --}}
                <div class="flex items-start justify-between gap-3 mb-3 sm:mb-6">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap mb-1">
                            <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-gray-900 leading-tight">{{ $property->name }}</h1>
                            @php $availableBeds = $property->rooms->flatMap->beds->where('status','available')->count(); @endphp
                            @if($availableBeds > 0)
                                <span class="px-2 py-0.5 bg-green-100 text-green-700 text-[10px] sm:text-xs font-bold rounded-full flex items-center gap-1 whitespace-nowrap">
                                    <span class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></span> Available
                                </span>
                            @else
                                <span class="px-2 py-0.5 bg-red-100 text-red-700 text-[10px] sm:text-xs font-bold rounded-full whitespace-nowrap">● Full</span>
                            @endif
                        </div>
                        <p class="text-gray-500 flex items-center gap-1.5 text-xs sm:text-sm">
                            <svg class="w-3.5 h-3.5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            {{ $property->full_address }}
                        </p>
                    </div>
                </div>

                {{-- Price --}}
                @php $startingRent = $property->rooms->flatMap->beds->whereNotNull('rental_rate')->min('rental_rate'); @endphp
                @if($startingRent)
                    <div class="bg-gradient-to-r from-indigo-50 to-indigo-100/50 rounded-xl p-3 sm:p-5 mb-4 sm:mb-6 border border-indigo-100">
                        <p class="text-xs text-indigo-600 font-medium mb-0.5">Starting from</p>
                        <p class="text-2xl sm:text-4xl font-extrabold text-indigo-700">₱{{ number_format($startingRent, 0) }}<span class="text-sm sm:text-lg text-gray-400 font-normal"> /month</span></p>
                    </div>
                @endif

                {{-- Stats --}}
                <div class="flex flex-wrap gap-2 sm:gap-4 text-xs sm:text-sm text-gray-600 mb-4 sm:mb-6">
                    <span class="flex items-center gap-1.5 bg-gray-50 px-3 py-2 rounded-lg">
                        <span class="text-base">🛏</span>
                        <span><strong class="text-gray-900">{{ $availableBeds }}</strong> beds</span>
                    </span>
                    <span class="flex items-center gap-1.5 bg-gray-50 px-3 py-2 rounded-lg">
                        <span class="text-base">🚪</span>
                        <span><strong class="text-gray-900">{{ $property->rooms->count() }}</strong> rooms</span>
                    </span>
                </div>

                @if($property->public_description)
                    <p class="text-gray-700 text-xs sm:text-sm leading-relaxed whitespace-pre-line mb-4 sm:mb-6">{{ $property->public_description }}</p>
                @endif

                {{-- Amenities --}}
                @php $amenities = is_array($property->amenities) ? $property->amenities : []; @endphp
                @if(!empty($amenities))
                    <div class="mb-4 sm:mb-6">
                        <h2 class="font-bold text-gray-900 text-sm sm:text-base mb-2 sm:mb-3">✨ Amenities</h2>
                        <div class="flex flex-wrap gap-1.5 sm:gap-2">
                            @foreach($amenities as $amenity)
                                <span class="px-2.5 py-1 bg-indigo-50 text-indigo-700 text-xs font-medium rounded-lg border border-indigo-100">{{ $amenity }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- House Rules --}}
                @if($property->house_rules)
                    <div class="mb-4 sm:mb-6">
                        <h2 class="font-bold text-gray-900 text-sm sm:text-base mb-2 sm:mb-3">📋 House Rules</h2>
                        <div class="bg-gray-50 rounded-xl p-3 sm:p-5 border border-gray-200">
                            <p class="text-gray-700 text-xs sm:text-sm whitespace-pre-line leading-relaxed">{{ $property->house_rules }}</p>
                        </div>
                    </div>
                @endif

                {{-- Available Rooms --}}
                <div id="rooms">
                    <h2 class="font-bold text-gray-900 text-base sm:text-xl mb-3 sm:mb-4">🚪 Available Rooms</h2>

                    {{-- Grid: 2 cols, no side padding — cards go edge-to-edge on mobile --}}
                    <div class="grid grid-cols-2 gap-2 sm:gap-4">
                        @forelse($property->rooms as $room)
                            @php
                                $roomAvailable = $room->beds->where('status','available')->count();
                                $roomRate = $room->beds->whereNotNull('rental_rate')->min('rental_rate') ?? $room->rental_rate;
                            @endphp
                            <div class="bg-white rounded-xl sm:rounded-2xl border border-gray-200 hover:border-indigo-300 hover:shadow-lg transition-all duration-300 overflow-hidden group flex flex-col">

                                {{-- Photo --}}
                                <div class="relative w-full h-28 sm:h-44 bg-gray-100 flex-shrink-0">
                                    @if($room->photo)
                                        <img src="{{ asset('storage/' . $room->photo) }}" alt="{{ $room->display_name }}" loading="lazy"
                                             onerror="this.hidden=true; this.nextElementSibling.classList.remove('hidden');"
                                             class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                    @endif
                                    <div class="{{ $room->photo ? 'hidden ' : '' }}absolute inset-0 flex flex-col items-center justify-center gap-1 text-gray-400">
                                        <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                            <rect x="3.75" y="4.75" width="16.5" height="14.5" rx="2"></rect>
                                            <circle cx="9" cy="10" r="1.5"></circle>
                                            <path d="m4.5 17 5-5 3.5 3.5 2.5-2.5 4 4"></path>
                                        </svg>
                                        <span class="text-[9px] font-medium">No photo</span>
                                    </div>
                                    <div class="absolute top-1.5 right-1.5">
                                        @if($roomAvailable > 0)
                                            <span class="px-1.5 py-0.5 bg-green-500 text-white text-[8px] sm:text-xs font-bold rounded-md shadow flex items-center gap-0.5">
                                                <span class="w-1 h-1 bg-white rounded-full"></span>
                                                {{ $roomAvailable }}
                                            </span>
                                        @else
                                            <span class="px-1.5 py-0.5 bg-gray-500 text-white text-[8px] sm:text-xs font-bold rounded-md shadow">Full</span>
                                        @endif
                                    </div>
                                </div>

                                {{-- Info --}}
                                <div class="flex flex-col flex-1 p-2.5 sm:p-4">
                                    <h3 class="font-bold text-gray-900 text-[11px] sm:text-sm leading-tight mb-0.5 group-hover:text-indigo-700 transition-colors line-clamp-1">{{ $room->display_name }}</h3>
                                    <p class="text-gray-400 text-[9px] sm:text-xs capitalize mb-1.5">{{ $room->room_type }} · Fl. {{ $room->floor }}</p>

                                    @if($roomRate)
                                        <p class="text-sm sm:text-lg font-extrabold text-indigo-700 leading-tight">₱{{ number_format($roomRate, 0) }}<span class="text-[9px] sm:text-xs text-gray-400 font-normal"> /mo</span></p>
                                    @endif
                                    <p class="text-[9px] sm:text-xs text-gray-500 mt-0.5 mb-2.5">{{ $roomAvailable }} {{ Str::plural('bed', $roomAvailable) }}</p>

                                    {{-- Button pinned to bottom --}}
                                    <div class="mt-auto">
                                        <a href="{{ route('houses.room', [$property, $room]) }}"
                                           class="flex items-center justify-center gap-1 w-full py-2 sm:py-2.5 text-[10px] sm:text-sm font-bold text-white rounded-lg transition
                                                  {{ $roomAvailable > 0 ? 'bg-indigo-600 hover:bg-indigo-700' : 'bg-gray-400' }}">
                                            {{ $roomAvailable > 0 ? 'View Beds' : 'View Room' }}
                                            <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="text-gray-500 text-xs col-span-2 text-center py-8">No rooms listed yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Divider on mobile between main content and sidebar --}}
            <div class="block sm:hidden h-2 bg-gray-100"></div>

            {{-- Contact + Map + CTA stacked on mobile inside main column --}}
            <div class="block lg:hidden px-4 sm:px-0 pb-6 space-y-4">
                @if($property->public_contact)
                    <div class="bg-white rounded-2xl border border-gray-200 p-4">
                        <h3 class="font-bold text-gray-900 text-sm mb-2">📞 Contact</h3>
                        <a href="tel:{{ $property->public_contact }}"
                           class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl hover:bg-indigo-50 transition">
                            <span class="text-sm font-medium text-gray-700">{{ $property->public_contact }}</span>
                        </a>
                    </div>
                @endif

                <div class="bg-white rounded-2xl border border-gray-200 p-4">
                    <h3 class="font-bold text-gray-900 text-sm mb-2">📍 Location</h3>
                    <p class="text-gray-600 text-xs mb-3">{{ $property->full_address }}</p>
                    @if($property->latitude && $property->longitude)
                        <div class="rounded-xl overflow-hidden mb-3 bg-gray-100 h-40 border border-gray-200">
                            <iframe src="https://maps.google.com/maps?q={{ $property->latitude }},{{ $property->longitude }}&z=15&output=embed"
                                    class="w-full h-full border-0" loading="lazy" allowfullscreen></iframe>
                        </div>
                    @endif
                    <a href="{{ $property->google_maps_url }}" target="_blank"
                       class="flex items-center justify-center gap-2 w-full py-2.5 text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition">
                        View on Google Maps
                    </a>
                </div>

                @if($availableBeds > 0)
                    <div class="bg-gradient-to-br from-indigo-600 to-indigo-700 rounded-2xl p-5 text-center shadow-xl">
                        <p class="text-white font-bold text-lg mb-1">{{ $availableBeds }} beds available</p>
                        <p class="text-indigo-100 text-xs mb-4">Select a room above to request a bed.</p>
                        <a href="#rooms" class="block w-full py-3 text-sm font-bold text-indigo-700 bg-white hover:bg-gray-100 rounded-xl transition shadow-lg">
                            Request to Rent
                        </a>
                    </div>
                @endif
            </div>
        </div>

        {{-- Right Sidebar — desktop only --}}
        <div class="hidden lg:block space-y-5">
            @if($property->public_contact)
                <div class="bg-white rounded-2xl border border-gray-200 p-6">
                    <h3 class="font-bold text-gray-900 mb-3 flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        Contact
                    </h3>
                    <a href="tel:{{ $property->public_contact }}"
                       class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl hover:bg-indigo-50 transition group">
                        <div class="w-10 h-10 bg-indigo-100 text-indigo-700 rounded-lg flex items-center justify-center group-hover:bg-indigo-200 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        </div>
                        <span class="text-sm font-medium text-gray-700 group-hover:text-indigo-700">{{ $property->public_contact }}</span>
                    </a>
                </div>
            @endif

            <div class="bg-white rounded-2xl border border-gray-200 p-6">
                <h3 class="font-bold text-gray-900 mb-3 flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Location
                </h3>
                <p class="text-gray-600 text-sm mb-3">{{ $property->full_address }}</p>
                @if($property->latitude && $property->longitude)
                    <div class="rounded-xl overflow-hidden mb-3 bg-gray-100 h-48 border border-gray-200">
                        <iframe src="https://maps.google.com/maps?q={{ $property->latitude }},{{ $property->longitude }}&z=15&output=embed"
                                class="w-full h-full border-0" loading="lazy" allowfullscreen></iframe>
                    </div>
                @endif
                <a href="{{ $property->google_maps_url }}" target="_blank"
                   class="flex items-center justify-center gap-2 w-full py-2.5 text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition shadow-md hover:shadow-lg">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    View on Google Maps
                </a>
            </div>

            @if($availableBeds > 0)
                <div class="bg-gradient-to-br from-indigo-600 to-indigo-700 rounded-2xl p-6 text-center shadow-xl sticky top-24">
                    <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center text-2xl mx-auto mb-3">🛏</div>
                    <p class="text-white font-bold text-xl mb-1">{{ $availableBeds }} beds available</p>
                    <p class="text-indigo-100 text-sm mb-5">Select a room to request a bed.</p>
                    <a href="#rooms" class="block w-full py-3 text-base font-bold text-indigo-700 bg-white hover:bg-gray-100 rounded-xl transition shadow-lg">
                        Request to Rent
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
