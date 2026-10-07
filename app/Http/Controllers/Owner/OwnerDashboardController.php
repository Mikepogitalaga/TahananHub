<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Bed;
use App\Models\Expense;
use App\Models\MaintenanceRequest;
use App\Models\OwnerAccessRequest;
use App\Models\OwnerAccessSession;
use App\Models\Property;
use App\Models\RentalContract;
use App\Models\RentBilling;
use App\Models\RentPayment;
use App\Models\Room;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\View\View;

class OwnerDashboardController extends Controller
{
    public function index(): View
    {
        $owner = auth()->user()->houseOwner;

        if (! $owner) {
            abort(403, 'No House Owner profile found for this account.');
        }

        $propertyIds = $owner->properties()->pluck('id');
        $roomIds = Room::whereIn('property_id', $propertyIds)->pluck('id');

        $totalProperties = $propertyIds->count();
        $totalRooms      = $roomIds->count();
        $totalBeds       = Bed::whereIn('room_id', $roomIds)->count();
        $occupiedBeds    = Bed::whereIn('room_id', $roomIds)->where('status', 'occupied')->count();
        $availableBeds   = Bed::whereIn('room_id', $roomIds)->where('status', 'available')->count();
        $activeTenants   = $owner->tenants()->where('status', 'active')->count();

        // Financials for Current Month
        $currentMonth = Carbon::now()->startOfMonth();
        $monthlyExpected = RentalContract::whereIn('property_id', $propertyIds)
            ->where('status', 'active')
            ->sum('monthly_rent');

        $monthlyCollected = RentPayment::whereHas('billing.contract', function ($q) use ($propertyIds) {
                $q->whereIn('property_id', $propertyIds);
            })
            ->where('payment_date', '>=', $currentMonth->toDateString())
            ->sum('amount');

        $outstandingBalance = RentBilling::whereHas('contract', function ($q) use ($propertyIds) {
                $q->whereIn('property_id', $propertyIds);
            })
            ->whereIn('status', ['unpaid', 'partial', 'overdue'])
            ->sum('balance');

        $monthlyExpenses = Expense::where('house_owner_id', $owner->id)
            ->where('expense_date', '>=', $currentMonth->toDateString())
            ->sum('amount');

        $netIncome = $monthlyCollected - $monthlyExpenses;

        $pendingMaintenance = MaintenanceRequest::whereIn('property_id', $propertyIds)
            ->whereIn('status', ['pending', 'in_progress'])
            ->count();

        // Privacy Access Alert
        $pendingAdminRequests = OwnerAccessRequest::where('house_owner_id', $owner->id)
            ->where('status', 'pending')
            ->count();

        $activeAdminSessions = OwnerAccessSession::where('house_owner_id', $owner->id)
            ->where('is_active', true)
            ->where('expires_at', '>', Carbon::now())
            ->count();

        $recentPayments = RentPayment::whereHas('billing.contract', function ($q) use ($propertyIds) {
                $q->whereIn('property_id', $propertyIds);
            })
            ->with(['tenant', 'billing'])
            ->latest()
            ->take(5)
            ->get();

        return view('owner.dashboard', compact(
            'owner',
            'totalProperties',
            'totalRooms',
            'totalBeds',
            'occupiedBeds',
            'availableBeds',
            'activeTenants',
            'monthlyExpected',
            'monthlyCollected',
            'outstandingBalance',
            'monthlyExpenses',
            'netIncome',
            'pendingMaintenance',
            'pendingAdminRequests',
            'activeAdminSessions',
            'recentPayments'
        ));
    }
}

