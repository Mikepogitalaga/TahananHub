<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class TenantProfileController extends Controller
{
    public function show(): View
    {
        $tenant = auth()->user()->tenantProfile;

        if (!$tenant) {
            return view('tenant.no-profile');
        }

        $assignment = $tenant->currentAssignment()
            ->with(['property.houseOwner', 'room', 'bed'])
            ->first();

        return view('tenant.profile.show', compact('tenant', 'assignment'));
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'confirmed', Password::defaults()],
        ]);

        auth()->user()->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Your account password has been updated securely.');
    }
}

