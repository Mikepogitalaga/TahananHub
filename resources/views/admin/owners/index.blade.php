@extends('layouts.admin')

@section('page_title', 'House Owners')

@section('content')
<div class="space-y-6">

    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-gray-900">House Owner Businesses</h2>
            <p class="text-sm text-gray-500">Boarding house owners operating isolated business workspaces on the platform.</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
        <form method="GET" action="{{ route('admin.owners.index') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search business or owner name..."
                   class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">

            <select name="plan" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                <option value="">All Subscription Plans</option>
                <option value="free" {{ request('plan') == 'free' ? 'selected' : '' }}>Free</option>
                <option value="basic" {{ request('plan') == 'basic' ? 'selected' : '' }}>Basic</option>
                <option value="standard" {{ request('plan') == 'standard' ? 'selected' : '' }}>Standard</option>
                <option value="premium" {{ request('plan') == 'premium' ? 'selected' : '' }}>Premium</option>
            </select>

            <div class="flex gap-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-gray-800 hover:bg-gray-900 text-white rounded-lg text-sm font-medium">Filter</button>
                <a href="{{ route('admin.owners.index') }}" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-medium">Reset</a>
            </div>
        </form>
    </div>

    {{-- Owners Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse($owners as $owner)
            <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm hover:border-blue-300 transition flex flex-col justify-between">
                <div>
                    <div class="flex items-start justify-between mb-3">
                        <div class="w-10 h-10 rounded-lg bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold text-lg">
                            🏢
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold uppercase
                            {{ $owner->subscription_plan === 'premium' ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-700' }}">
                            {{ $owner->subscription_plan }}
                        </span>
                    </div>

                    <h3 class="font-bold text-gray-900 text-base mb-1">
                        {{ $owner->business_name ?? 'Unnamed Business' }}
                    </h3>
                    <p class="text-xs text-gray-500 mb-3">
                        Owner: <span class="text-gray-700 font-medium">{{ $owner->user?->name }}</span>
                    </p>

                    <div class="grid grid-cols-2 gap-2 text-xs bg-gray-50 p-2.5 rounded-lg mb-4 text-gray-600">
                        <div>
                            <span class="text-gray-400 block text-[10px]">Properties</span>
                            <span class="font-bold text-gray-800">{{ $owner->properties->count() }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400 block text-[10px]">Account Status</span>
                            <span class="font-semibold {{ $owner->user?->status === 'active' ? 'text-emerald-600' : 'text-red-600' }}">
                                {{ ucfirst($owner->user?->status ?? 'unknown') }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="pt-3 border-t border-gray-100 flex items-center justify-between">
                    <span class="text-[11px] text-gray-400">Joined {{ $owner->created_at->format('M Y') }}</span>
                    <a href="{{ route('admin.owners.show', $owner) }}"
                       class="px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-semibold rounded-lg transition">
                        View Workspace →
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-3 bg-white rounded-xl border border-gray-200 p-8 text-center text-gray-400">
                No house owners registered on the platform yet.
            </div>
        @endforelse
    </div>

    @if($owners->hasPages())
        <div>{{ $owners->links() }}</div>
    @endif

</div>
@endsection

