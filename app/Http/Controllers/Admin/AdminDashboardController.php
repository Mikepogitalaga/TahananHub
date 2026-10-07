<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HouseOwner;
use App\Models\OwnerAccessRequest;
use App\Models\OwnerAccessSession;
use App\Models\Property;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(): View
    {
        // Platform-level metrics ONLY — No private owner financials or tenant personal data!
        $totalUsers = User::count();
        $totalOwners = HouseOwner::count();
        $activeOwners = User::where('role', User::ROLE_OWNER)->where('status', 'active')->count();
        $suspendedUsers = User::where('status', 'suspended')->count();
        $totalProperties = Property::count();

        $activeSessions = OwnerAccessSession::where('is_active', true)
            ->where('expires_at', '>', Carbon::now())
            ->with(['houseOwner.user', 'adminUser'])
            ->get();

        $recentRequests = OwnerAccessRequest::with('houseOwner.user')
            ->latest()
            ->take(5)
            ->get();

        $recentUsers = User::latest()->take(5)->get();

        return view('admin.dashboard', compact(
            'totalUsers',
            'totalOwners',
            'activeOwners',
            'suspendedUsers',
            'totalProperties',
            'activeSessions',
            'recentRequests',
            'recentUsers'
        ));
    }
}

