<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HouseOwner;
use App\Models\OwnerAccessSession;
use App\Services\PrivacyAccessService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminOwnerController extends Controller
{
    public function __construct(private PrivacyAccessService $privacyService) {}

    public function index(Request $request): View
    {
        $query = HouseOwner::with(['user', 'properties'])->latest();

        if ($request->filled('plan')) {
            $query->where('subscription_plan', $request->plan);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('business_name', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $owners = $query->paginate(12)->withQueryString();

        return view('admin.owners.index', compact('owners'));
    }

    public function show(HouseOwner $owner): View
    {
        $owner->load(['user', 'properties']);

        $admin = auth()->user();
        $activeSession = $this->privacyService->getActiveSession($admin, $owner);

        // Check granular permissions for this owner
        $hasPropertyAccess = $activeSession && in_array('VIEW_PROPERTY', $activeSession->permissions, true);
        $hasTenantAccess   = $activeSession && in_array('VIEW_TENANTS', $activeSession->permissions, true);
        $hasPaymentAccess  = $activeSession && in_array('VIEW_PAYMENTS', $activeSession->permissions, true);
        $hasIncomeAccess   = $activeSession && in_array('VIEW_INCOME', $activeSession->permissions, true);

        // If granted, load permitted private collections
        $properties = $hasPropertyAccess ? $owner->properties()->with('rooms.beds')->get() : collect();
        $tenants    = $hasTenantAccess ? $owner->tenants()->latest()->get() : collect();

        return view('admin.owners.show', compact(
            'owner',
            'activeSession',
            'hasPropertyAccess',
            'hasTenantAccess',
            'hasPaymentAccess',
            'hasIncomeAccess',
            'properties',
            'tenants'
        ));
    }

    public function edit(HouseOwner $owner): View
    {
        return view('admin.owners.edit', compact('owner'));
    }

    public function update(Request $request, HouseOwner $owner): RedirectResponse
    {
        $validated = $request->validate([
            'business_name'     => ['nullable', 'string', 'max:255'],
            'business_address'  => ['nullable', 'string', 'max:500'],
            'subscription_plan' => ['required', 'in:free,basic,standard,premium'],
        ]);

        $owner->update($validated);

        return redirect()->route('admin.owners.show', $owner)->with('success', 'House Owner settings updated.');
    }
}

