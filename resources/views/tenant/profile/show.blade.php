@extends('layouts.tenant')

@section('title', 'My Profile')

@section('content')
<div class="space-y-6 max-w-4xl">
    <div>
        <h1 class="text-2xl font-extrabold text-slate-900">My Profile</h1>
        <p class="text-sm text-slate-500 mt-1">Your personal information and account security.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        {{-- Personal Information --}}
        <section class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-4">Personal Information</h2>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-500">Full Name</dt>
                    <dd class="font-semibold text-slate-900 text-right">{{ $tenant->full_name }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-500">Contact Number</dt>
                    <dd class="font-semibold text-slate-900 text-right">{{ $tenant->contact_number ?? '—' }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-500">Email</dt>
                    <dd class="font-semibold text-slate-900 text-right">{{ $tenant->email ?? auth()->user()->email }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-500">Address</dt>
                    <dd class="font-semibold text-slate-900 text-right">{{ $tenant->address ?? '—' }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-500">Status</dt>
                    <dd><span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-emerald-50 text-emerald-700 border border-emerald-200">{{ $tenant->status }}</span></dd>
                </div>
            </dl>
        </section>

        {{-- Assignment --}}
        <section class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-4">Current Assignment</h2>
            @if($assignment)
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between gap-4">
                        <dt class="text-slate-500">Property</dt>
                        <dd class="font-semibold text-slate-900 text-right">{{ $assignment->property?->name }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-slate-500">Room</dt>
                        <dd class="font-semibold text-slate-900 text-right">{{ $assignment->room?->room_number }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-slate-500">Bed</dt>
                        <dd class="font-semibold text-slate-900 text-right">{{ $assignment->bed?->bed_number ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-slate-500">Assigned Since</dt>
                        <dd class="font-semibold text-slate-900 text-right">{{ \Carbon\Carbon::parse($assignment->assigned_at)->format('M d, Y') }}</dd>
                    </div>
                </dl>
            @else
                <p class="text-sm text-slate-400 text-center py-6">No active room assignment yet.</p>
            @endif
        </section>
    </div>

    {{-- Password Change --}}
    <section class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-4">Change Password</h2>
        <form method="POST" action="{{ route('tenant.profile.password') }}" class="space-y-4 max-w-md">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Current Password</label>
                <input type="password" name="current_password" required
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                @error('current_password')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">New Password</label>
                <input type="password" name="password" required
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                @error('password')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Confirm New Password</label>
                <input type="password" name="password_confirmation" required
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
            </div>
            <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-semibold transition">
                Update Password
            </button>
        </form>
    </section>
</div>
@endsection
