<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\View\View;

class TenantAnnouncementController extends Controller
{
    public function index(): View
    {
        $tenant = auth()->user()->tenantProfile;

        if (!$tenant) {
            return view('tenant.no-profile');
        }

        $assignment = $tenant->currentAssignment()->first();
        $propertyId = $assignment?->property_id;

        $announcements = Announcement::where('status', 'active')
            ->where(function ($q) use ($propertyId, $tenant) {
                $q->where('is_platform_wide', true)
                  ->orWhere('house_owner_id', $tenant->house_owner_id)
                  ->orWhere('property_id', $propertyId);
            })
            ->latest('publish_at')
            ->paginate(10);

        return view('tenant.announcements.index', compact('tenant', 'announcements'));
    }
}

