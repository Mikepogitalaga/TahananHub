@extends('layouts.tenant')

@section('title', 'Profile Pending')

@section('content')
<div class="max-w-md mx-auto my-12 bg-white border border-slate-200 rounded-2xl p-8 text-center shadow-sm">
    <div class="text-4xl mb-3">🏡</div>
    <h2 class="text-xl font-bold text-slate-800">Tenant Profile Linking Required</h2>
    <p class="text-sm text-slate-500 mt-2">Your tenant account is registered, but your boarding house landlord has not yet linked your profile to an active room or bed assignment.</p>
    <div class="mt-6 p-4 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-600 text-left">
        <span class="font-bold text-slate-700 block mb-1">What to do:</span>
        Contact your boarding house manager or landlord and give them your email: <span class="font-mono font-bold text-indigo-600">{{ auth()->user()->email }}</span> so they can assign your room.
    </div>
</div>
@endsection

