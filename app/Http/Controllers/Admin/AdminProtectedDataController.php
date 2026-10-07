<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\HouseOwner;
use App\Models\Property;
use App\Models\RentBilling;
use App\Models\RentPayment;
use App\Models\RentalContract;
use App\Models\Tenant;
use App\Services\PrivacyAccessService;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * READ-ONLY viewer for House Owner private business data.
 *
 * Every route using this controller is wrapped by the `privacy:PERMISSION`
 * middleware, which DENIES access unless the Super Admin holds an active,
 * unexpired, owner-approved access session containing that exact permission.
 * This controller must never perform writes.
 */
class AdminProtectedDataController extends Controller
{
    public function __construct(private PrivacyAccessService $privacy) {}

    public function properties(Request $request, HouseOwner $owner): View
    {
        $properties = Property::where('house_owner_id', $owner->id)
            ->withCount('rooms')
            ->orderBy('name')
            ->get();

        return $this->render($request, $owner, 'properties', compact('properties'));
    }

    public function rooms(Request $request, HouseOwner $owner): View
    {
        $rooms = Property::where('house_owner_id', $owner->id)
            ->with(['rooms' => fn ($q) => $q->orderBy('room_number')])
            ->get();

        return $this->render($request, $owner, 'rooms', compact('rooms'));
    }

    public function tenants(Request $request, HouseOwner $owner): View
    {
        $tenants = Tenant::where('house_owner_id', $owner->id)
            ->with('currentAssignment.room')
            ->orderBy('last_name')
            ->get();

        // Contact details require the additional granular permission.
        $canSeeContact = $this->hasPermission($request, $owner, 'VIEW_TENANT_CONTACT');

        return $this->render($request, $owner, 'tenants', compact('tenants', 'canSeeContact'));
    }

    public function contracts(Request $request, HouseOwner $owner): View
    {
        $contracts = RentalContract::whereHas('tenant', fn ($q) => $q->where('house_owner_id', $owner->id))
            ->with(['tenant', 'property', 'room'])
            ->latest('start_date')
            ->get();

        return $this->render($request, $owner, 'contracts', compact('contracts'));
    }

    public function billings(Request $request, HouseOwner $owner): View
    {
        $billings = RentBilling::whereHas('tenant', fn ($q) => $q->where('house_owner_id', $owner->id))
            ->with(['tenant', 'contract'])
            ->latest('billing_period_start')
            ->paginate(50)->withQueryString();

        return $this->render($request, $owner, 'billings', compact('billings'));
    }

    public function payments(Request $request, HouseOwner $owner): View
    {
        $payments = RentPayment::whereHas('tenant', fn ($q) => $q->where('house_owner_id', $owner->id))
            ->with(['tenant', 'billing', 'recordedBy'])
            ->latest('payment_date')
            ->paginate(50)->withQueryString();

        return $this->render($request, $owner, 'payments', compact('payments'));
    }

    public function expenses(Request $request, HouseOwner $owner): View
    {
        $expenses = Expense::where('house_owner_id', $owner->id)
            ->with('property')
            ->latest('expense_date')
            ->paginate(50)->withQueryString();

        return $this->render($request, $owner, 'expenses', compact('expenses'));
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function hasPermission(Request $request, HouseOwner $owner, string $permission): bool
    {
        return $this->privacy->checkAdminHasAccess($request->user(), $owner, $permission);
    }

    /**
     * Render a protected section with the active session metadata for the view.
     */
    private function render(Request $request, HouseOwner $owner, string $section, array $data): View
    {
        $session = $this->privacy->getActiveSession($request->user(), $owner);

        return view("admin.protected.{$section}", $data + [
            'owner'         => $owner,
            'activeSession' => $session,
            'section'       => $section,
        ]);
    }
}
