<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\RentalContract;
use Illuminate\View\View;

class TenantContractController extends Controller
{
    public function index(): View
    {
        $tenant = auth()->user()->tenantProfile;

        if (!$tenant) {
            return view('tenant.no-profile');
        }

        $contracts = $tenant->rentalContracts()
            ->with(['property.houseOwner', 'room', 'bed'])
            ->latest('start_date')
            ->get();

        return view('tenant.contracts.index', compact('tenant', 'contracts'));
    }

    public function show(RentalContract $contract): View
    {
        $tenant = auth()->user()->tenantProfile;

        if (!$tenant || $contract->tenant_id !== $tenant->id) {
            abort(403, 'Unauthorized.');
        }

        $contract->load(['property.houseOwner.user', 'room', 'bed']);

        return view('tenant.contracts.show', compact('tenant', 'contract'));
    }
}

