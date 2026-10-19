@extends('layouts.public')

@section('title', 'Browse Houses — TahananHub')

@section('content')

{{-- Hero --}}
<section class="relative bg-gradient-to-br from-indigo-700 via-indigo-600 to-indigo-500 text-white py-16 sm:py-24 px-4 overflow-hidden">
    <div class="absolute inset-0 opacity-10">
        <svg class="w-full h-full" viewBox="0 0 100 100" preserveAspectRatio="none">
            <pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse">
                <path d="M 10 0 L 0 0 0 10" fill="none" stroke="white" stroke-width="0.5"/>
            </pattern>
            <rect width="100" height="100" fill="url(#grid)"/>
        </svg>
    </div>
    <div class="max-w-4xl mx-auto text-center relative">
        <div class="inline-flex items-center gap-2 px-4 py-1.5 bg-white/10 backdrop-blur-sm rounded-full text-xs sm:text-sm font-medium mb-6 border border-white/20">
            <span class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></span>
            {{ $properties->total() }} properties available now
        </div>
        <h1 class="text-3xl sm:text-4xl lg:text-6xl font-extrabold mb-3 tracking-tight">Find Your Next Home</h1>
        <p class="text-indigo-100 text-sm sm:text-xl mb-8 sm:mb-10 max-w-2xl mx-auto">Discover verified boarding houses, rooms, and beds with transparent pricing.</p>

        <form method="GET" action="{{ route('houses.index') }}" class="bg-white rounded-2xl shadow-2xl p-4 sm:p-6 text-left">
            <div class="flex flex-col sm:flex-row gap-3 mb-4">
                <div class="relative flex-1">
                    <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 sm:w-5 sm:h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search location, barangay, or house name..." class="w-full pl-11 sm:pl-12 pr-4 py-2.5 sm:py-3.5 border border-gray-300 rounded-lg text-gray-900 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                </div>
                <button type="submit" class="px-6 py-3 sm:px-8 sm:py-3.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-lg text-xs sm:text-sm transition shadow-lg hover:shadow-xl flex items-center justify-center gap-1.5 sm:gap-2">
                    <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    Search
                </button>
            </div>
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1.5 ml-1">Location</label>
                    <select name="municipality" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-gray-700 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        <option value="">All Locations</option>
                        @foreach($municipalities as $m)
                            <option value="{{ $m }}" {{ request('municipality') === $m ? 'selected' : '' }}>{{ $m }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1.5 ml-1">Room Type</label>
                    <select name="room_type" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-gray-700 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        <option value="">Any Type</option>
                        @foreach(['single','double','shared','dormitory','studio'] as $type)
                            <option value="{{ $type }}" {{ request('room_type') === $type ? 'selected' : '' }}>{{ ucfirst($type) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1.5 ml-1">Min Price (₱)</label>
                    <input type="number" name="min_price" value="{{ request('min_price') }}" placeholder="0" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-gray-700 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1.5 ml-1">Max Price (₱)</label>
                    <input type="number" name="max_price" value="{{ request('max_price') }}" placeholder="Any" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-gray-700 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                </div>
            </div>
        </form>
    </div>
</section>

{{-- Results --}}
<section class="max-w-7xl mx-auto px-4 sm:px-6 py-10">
    <div class="flex items-center justify-between mb-6">
        <p class="text-gray-600 text-sm">
            {{ $properties->total() }} {{ Str::plural('property', $properties->total()) }} found
            @if(request()->hasAny(['search','municipality','room_type','min_price','max_price']))
                — <a href="{{ route('houses.index') }}" class="text-indigo-600 hover:text-indigo-700 font-semibold">Clear filters</a>
            @endif
        </p>
    </div>

    @if($properties->isEmpty())
        <div class="text-center py-20 bg-white rounded-2xl border border-gray-200">
            <div class="text-7xl mb-4">🏠</div>
            <h3 class="text-2xl font-bold text-gray-900 mb-2">No properties found</h3>
            <p class="text-gray-500 mb-6 max-w-md mx-auto">We couldn't find any properties matching your criteria. Try adjusting your search or filters.</p>
            <a href="{{ route('houses.index') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                View All Properties
            </a>
        </div>
    @else
        <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-6">
            @foreach($properties as $property)
                @php
                    $availableBeds = $property->rooms->flatMap->beds->where('status', 'available')->count();
                    $cover = $property->photos->firstWhere('is_cover', true) ?? $property->photos->first();
                    $amenities = is_array($property->amenities) ? $property->amenities : [];
                    $startingRent = $property->rooms->flatMap->beds->whereNotNull('rental_rate')->min('rental_rate');
                    $isFeatured = $loop->iteration <= 3;
                @endphp
                <div class="group bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-xl hover:border-indigo-300 transition-all duration-300 flex flex-col {{ $isFeatured ? 'ring-2 ring-indigo-100' : '' }}">
                    {{-- Photo --}}
                    <div class="relative h-32 sm:h-48 bg-gradient-to-br from-indigo-100 to-indigo-200 flex items-center justify-center overflow-hidden">
                        @if($cover)
                            <img src="{{ asset('storage/' . $cover->path) }}" alt="{{ $property->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        @else
                            <span class="text-4xl sm:text-6xl">🏠</span>
                        @endif
                        <div class="absolute top-2 left-2 sm:top-3 sm:left-3 flex flex-col gap-1.5 sm:gap-2">
                            @if($isFeatured)
                                <span class="px-1.5 py-0.5 sm:px-2.5 sm:py-1 bg-amber-400 text-amber-900 text-[9px] sm:text-xs font-bold rounded-lg shadow-lg flex items-center gap-1">
                                    <svg class="w-2.5 h-2.5 sm:w-3 sm:h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                    Featured
                                </span>
                            @endif
                            @if($availableBeds > 0)
                                <span class="px-1.5 py-0.5 sm:px-2.5 sm:py-1 bg-green-500 text-white text-[9px] sm:text-xs font-bold rounded-lg shadow-lg flex items-center gap-1">
                                    <span class="w-1 h-1 sm:w-1.5 sm:h-1.5 bg-white rounded-full animate-pulse"></span>
                                    Available
                                </span>
                            @else
                                <span class="px-1.5 py-0.5 sm:px-2.5 sm:py-1 bg-red-500 text-white text-[9px] sm:text-xs font-bold rounded-lg shadow-lg">Full</span>
                            @endif
                        </div>
                        @if($availableBeds > 0)
                            <div class="absolute bottom-1 right-2 sm:bottom-3 sm:right-3 bg-white/90 backdrop-blur-sm px-1.5 py-0.5 sm:px-2.5 sm:py-1 rounded-lg text-[9px] sm:text-xs font-bold text-gray-900">
                                {{ $availableBeds }} beds
                            </div>
                        @endif
                    </div>

                    <div class="p-3 sm:p-5 flex flex-col flex-1">
                        <div class="flex items-start justify-between mb-2">
                            <div class="flex-1 min-w-0">
                                <h3 class="font-bold text-gray-900 text-sm sm:text-base mb-0.5 sm:mb-1 truncate group-hover:text-indigo-700 transition-colors">{{ $property->name }}</h3>
                                <p class="text-gray-500 text-[10px] sm:text-xs flex items-center gap-1">
                                    <svg class="w-2.5 h-2.5 sm:w-3.5 sm:h-3.5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    <span class="truncate">{{ collect([$property->barangay, $property->municipality])->filter()->implode(', ') }}</span>
                                </p>
                            </div>
                        </div>

                        @if($startingRent)
                            <div class="flex items-baseline gap-1.5 sm:gap-2 mb-2 sm:mb-3">
                                <p class="text-lg sm:text-2xl font-bold text-indigo-700">₱{{ number_format($startingRent, 0) }}</p>
                                <span class="text-gray-400 text-[9px] sm:text-xs font-medium">/month starting</span>
                            </div>
                        @endif

                        <div class="flex flex-wrap gap-1 mb-2 sm:mb-3">
                            @foreach(array_slice($amenities, 0, 2) as $amenity)
                                <span class="px-1.5 py-0.5 sm:px-2 sm:py-1 bg-gray-50 text-gray-600 text-[9px] sm:text-[10px] font-medium rounded border border-gray-100">{{ $amenity }}</span>
                            @endforeach
                            @if(count($amenities) > 2)
                                <span class="px-1.5 py-0.5 sm:px-2 sm:py-1 bg-indigo-50 text-indigo-700 text-[9px] sm:text-[10px] font-medium rounded border border-indigo-100">+{{ count($amenities) - 2 }} more</span>
                            @endif
                        </div>

                        <div class="mt-auto flex flex-col gap-1.5 sm:flex-row sm:gap-2 pt-3 sm:pt-4 border-t border-gray-100">
                            <a href="{{ route('houses.show', $property) }}"
                               class="flex-1 text-center px-3 py-2 sm:px-4 sm:py-2.5 text-[11px] sm:text-sm font-semibold text-indigo-700 border border-indigo-200 rounded-lg hover:bg-indigo-50 transition flex items-center justify-center gap-1.5">
                                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                View
                            </a>
                            @if($availableBeds > 0)
                                <a href="{{ route('houses.show', $property) }}#rooms"
                                   class="flex-1 text-center px-3 py-2 sm:px-4 sm:py-2.5 text-[11px] sm:text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition shadow-md hover:shadow-lg flex items-center justify-center gap-1.5">
                                    Rent
                                    <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-10">
            {{ $properties->links() }}
        </div>
    @endif
</section>

@endsection
