<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\RentBilling;
use Illuminate\View\View;

class TenantDashboardController extends Controller
{
    public function index(): View
    {
        $tenant = auth()->user()->tenantProfile;

        if (!$tenant) {
            return view('tenant.no-profile');
        }

        $assignment = $tenant->currentAssignment()
            ->with(['property.houseOwner', 'room', 'bed'])
            ->first();

        $contract = $tenant->activeContract()
            ->with(['property', 'room', 'bed'])
            ->first();

        $unpaidBillings = RentBilling::where('tenant_id', $tenant->id)
            ->whereIn('status', ['unpaid', 'partial', 'overdue'])
            ->orderBy('due_date', 'asc')
            ->get();

        $totalBalance = $unpaidBillings->sum('balance');
        $nextDueBilling = $unpaidBillings->first();

        $recentPayments = $tenant->rentPayments()
            ->with('billing')
            ->latest('payment_date')
            ->take(5)
            ->get();

        $openMaintenance = $tenant->maintenanceRequests()
            ->whereIn('status', ['pending', 'acknowledged', 'in_progress'])
            ->latest()
            ->take(5)
            ->get();

        $propertyId = $assignment?->property_id;
        $announcements = Announcement::where('status', 'active')
            ->where(function ($q) use ($propertyId, $tenant) {
                $q->where('is_platform_wide', true)
                  ->orWhere('house_owner_id', $tenant->house_owner_id)
                  ->orWhere('property_id', $propertyId);
            })
            ->latest('publish_at')
            ->take(4)
            ->get();

        return view('tenant.dashboard', compact(
            'tenant',
            'assignment',
            'contract',
            'unpaidBillings',
            'totalBalance',
            'nextDueBilling',
            'recentPayments',
            'openMaintenance',
            'announcements'
        ));
    }
}

