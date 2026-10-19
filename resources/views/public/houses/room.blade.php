@extends('layouts.public')

@section('title', $room->display_name . ' — ' . $property->name . ' — TahananHub')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 py-8">

    {{-- Breadcrumb --}}
    <nav class="text-xs text-gray-500 mb-6 flex items-center gap-2 flex-wrap">
        <a href="{{ route('houses.index') }}" class="hover:text-indigo-600 flex items-center gap-1">
            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Browse
        </a>
        <span class="text-gray-300">/</span>
        <a href="{{ route('houses.show', $property) }}" class="hover:text-indigo-600 text-xs sm:text-sm">{{ $property->name }}</a>
        <span class="text-gray-300">/</span>
        <span class="text-gray-800 font-medium text-xs sm:text-sm truncate">{{ $room->display_name }}</span>
    </nav>

    {{-- Room Header Card --}}
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden mb-6">
        @if($room->photo)
            <div class="rounded-t-2xl overflow-hidden h-52 sm:h-80">
                <img src="{{ asset('storage/' . $room->photo) }}" alt="{{ $room->display_name }}" class="w-full h-full object-cover">
            </div>
        @endif
        <div class="p-4 sm:p-8">
            <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-3 sm:gap-4 mb-3 sm:mb-4">
                <div class="flex-1">
                    <div class="flex items-center gap-2 sm:gap-3 mb-1 sm:mb-2 flex-wrap">
                        <h1 class="text-xl sm:text-2xl lg:text-3xl font-extrabold text-gray-900">{{ $room->display_name }}</h1>
                        <span class="px-2 py-0.5 sm:px-3 sm:py-1 bg-indigo-100 text-indigo-700 text-[9px] sm:text-xs font-bold rounded-full uppercase">{{ $room->room_type }}</span>
                    </div>
                    <p class="text-gray-500 text-xs sm:text-base capitalize flex items-center gap-1.5 sm:gap-2">
                        <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        Floor {{ $room->floor }}
                    </p>
                </div>
                @if($room->rental_rate)
                    <div class="bg-gradient-to-br from-indigo-50 to-indigo-100/50 rounded-xl p-3 sm:p-4 border border-indigo-100">
                        <p class="text-xs text-indigo-600 font-medium mb-0.5">Monthly Rate</p>
                        <p class="text-2xl sm:text-3xl font-extrabold text-indigo-700">₱{{ number_format($room->rental_rate, 0) }}<span class="text-sm sm:text-base text-gray-400 font-medium">/mo</span></p>
                    </div>
                @endif
            </div>
            @if($room->description)
                <p class="text-gray-600 text-sm sm:text-base mt-2 leading-relaxed">{{ $room->description }}</p>
            @endif
        </div>
    </div>

    {{-- Beds Section --}}
    <div class="flex items-center gap-3 mb-4">
        <h2 class="font-bold text-gray-900 text-xl sm:text-2xl">Available Beds</h2>
        <span class="px-2.5 py-1 bg-gray-100 text-gray-600 text-xs font-bold rounded-full">{{ $room->beds->count() }} total</span>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
    @forelse($room->beds as $bed)
        @php $isAvailable = $bed->status === 'available'; @endphp
        <article class="bg-white rounded-2xl border {{ $isAvailable ? 'border-gray-200 hover:border-indigo-300 hover:shadow-lg' : 'border-gray-100 opacity-70' }} overflow-hidden transition-all duration-300 flex flex-col {{ $isAvailable ? 'hover:-translate-y-1' : '' }}">
            <div class="relative w-full aspect-[4/3] bg-gray-100">
                @if($bed->photo)
                    <img src="{{ asset('storage/' . $bed->photo) }}" alt="{{ $bed->display_name }} photo" loading="lazy" decoding="async" onerror="this.hidden=true; this.nextElementSibling.classList.remove('hidden');" class="absolute inset-0 w-full h-full object-cover">
                @endif
                <div role="img" aria-label="No bed photo available" class="{{ $bed->photo ? 'hidden ' : '' }}absolute inset-0 text-gray-500 flex flex-col items-center justify-center gap-2">
                    <svg aria-hidden="true" class="w-8 h-8 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <rect x="3.75" y="4.75" width="16.5" height="14.5" rx="2"></rect>
                        <circle cx="9" cy="10" r="1.5"></circle>
                        <path d="m4.5 17 5-5 3.5 3.5 2.5-2.5 4 4"></path>
                    </svg>
                    <span class="text-[10px] font-medium">No photo</span>
                </div>
                <div class="absolute top-2 right-2">
                    @if($isAvailable)
                        <span class="px-2 py-1 bg-green-500 text-white text-[10px] font-bold rounded-lg shadow">AVAILABLE</span>
                    @elseif($bed->status === 'reserved')
                        <span class="px-2 py-1 bg-yellow-500 text-white text-[10px] font-bold rounded-lg shadow">RESERVED</span>
                    @elseif($bed->status === 'occupied')
                        <span class="px-2 py-1 bg-red-500 text-white text-[10px] font-bold rounded-lg shadow">OCCUPIED</span>
                    @else
                        <span class="px-2 py-1 bg-gray-500 text-white text-[10px] font-bold rounded-lg shadow uppercase">{{ $bed->status }}</span>
                    @endif
                </div>
            </div>

            <div class="p-2 sm:p-3 flex flex-col gap-1.5 sm:gap-2 flex-1">
                <div class="min-w-0">
                    <p class="font-bold text-gray-900 text-[11px] sm:text-sm truncate">{{ $bed->display_name }}</p>
                    @if($bed->rental_rate)
                        <p class="text-indigo-700 font-bold text-[10px] sm:text-sm">₱{{ number_format($bed->rental_rate, 0) }}<span class="text-gray-400 font-normal text-xs">/mo</span></p>
                    @endif
                </div>

                <div class="mt-auto">
                    @if($isAvailable)
                        @auth
                            <a href="{{ route('tenant.rental-requests.create', ['property' => $property->id, 'room' => $room->id, 'bed' => $bed->id]) }}"
                               class="flex items-center justify-center gap-1 w-full px-2.5 py-2 sm:py-2.5 text-[9px] sm:text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition shadow-sm hover:shadow-md">
                                <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                Rent
                            </a>
                        @else
                            <a href="{{ route('login') }}?intended={{ urlencode(route('tenant.rental-requests.create', ['property' => $property->id, 'room' => $room->id, 'bed' => $bed->id])) }}"
                               onclick="sessionStorage.setItem('intended', '{{ route('tenant.rental-requests.create', ['property' => $property->id, 'room' => $room->id, 'bed' => $bed->id]) }}')"
                               class="flex items-center justify-center gap-1 w-full px-2.5 py-2 sm:py-2.5 text-[9px] sm:text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition shadow-sm hover:shadow-md">
                                <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                Rent
                            </a>
                        @endauth
                    @endif
                </div>
            </div>
        </article>
    @empty
        <div class="col-span-full text-center py-16 bg-white rounded-2xl border border-gray-200">
            <div class="text-6xl mb-3">🛏</div>
            <p class="text-gray-500 font-medium">No beds listed for this room.</p>
        </div>
    @endforelse
    </div>

    @guest
        <div class="mt-8 sm:mt-10 bg-gradient-to-br from-indigo-50 to-indigo-100/50 border border-indigo-200 rounded-2xl p-5 sm:p-8 text-center">
            <div class="w-14 h-14 sm:w-16 sm:h-16 bg-indigo-100 text-indigo-700 rounded-full flex items-center justify-center text-2xl sm:text-3xl mx-auto mb-3 sm:mb-4">🔐</div>
            <p class="text-indigo-900 font-bold text-lg sm:text-xl mb-1 sm:mb-2">Want to rent a bed here?</p>
            <p class="text-indigo-700 text-xs sm:text-sm mb-4 sm:mb-6 max-w-md mx-auto">You need an account to submit a rental request. Join TahananHub today and find your perfect home.</p>
            <div class="flex flex-col sm:flex-row justify-center gap-2.5 sm:gap-3">
                <a href="{{ route('login') }}" class="px-5 py-2 sm:px-6 sm:py-3 text-xs sm:text-sm font-bold text-indigo-700 border border-indigo-400 rounded-lg hover:bg-indigo-100 transition">Login</a>
                <a href="{{ route('register') }}" class="px-5 py-2 sm:px-6 sm:py-3 text-xs sm:text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition shadow-lg">Create Account</a>
            </div>
        </div>
    @endguest

    <div class="mt-6 sm:mt-8">
        <a href="{{ route('houses.show', $property) }}" class="inline-flex items-center gap-2 text-indigo-600 hover:text-indigo-700 text-xs sm:text-sm font-medium">
            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back to {{ $property->name }}
        </a>
    </div>
</div>
@endsection
