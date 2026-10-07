<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceRequest;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TenantMaintenanceController extends Controller
{
    public function __construct(
        private ActivityLogService $activityLog
    ) {}

    public function index(): View
    {
        $tenant = auth()->user()->tenantProfile;

        if (!$tenant) {
            return view('tenant.no-profile');
        }

        $requests = $tenant->maintenanceRequests()
            ->with(['property', 'room', 'bed'])
            ->latest()
            ->paginate(15);

        return view('tenant.maintenance.index', compact('tenant', 'requests'));
    }

    public function create(): View
    {
        $tenant = auth()->user()->tenantProfile;

        if (!$tenant) {
            return view('tenant.no-profile');
        }

        $assignment = $tenant->currentAssignment()
            ->with(['property', 'room', 'bed'])
            ->first();

        return view('tenant.maintenance.create', compact('tenant', 'assignment'));
    }

    public function store(Request $request): RedirectResponse
    {
        $tenant = auth()->user()->tenantProfile;

        if (!$tenant) {
            abort(403, 'Tenant profile not linked.');
        }

        $assignment = $tenant->currentAssignment()->first();
        if (!$assignment) {
            return back()->with('error', 'You must have an active room/bed assignment to report maintenance issues.');
        }

        $validated = $request->validate([
            'category'    => ['required', 'in:plumbing,electrical,internet,furniture,room,bathroom,cleaning,other'],
            'priority'    => ['required', 'in:low,medium,high,urgent'],
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'photo'       => ['nullable', 'image', 'max:5120'],
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store("maintenance/{$assignment->property_id}", 'public');
        }

        $count = MaintenanceRequest::where('property_id', $assignment->property_id)->count() + 1;
        $reqNum = 'MNT-' . date('Ym') . '-' . str_pad((string)$count, 4, '0', STR_PAD_LEFT);

        $maintenance = MaintenanceRequest::create([
            'request_number' => $reqNum,
            'tenant_id'      => $tenant->id,
            'property_id'    => $assignment->property_id,
            'room_id'        => $assignment->room_id,
            'bed_id'         => $assignment->bed_id,
            'category'       => $validated['category'],
            'title'          => $validated['title'],
            'description'    => $validated['description'],
            'priority'       => $validated['priority'],
            'photo_path'     => $photoPath,
            'status'         => 'pending',
        ]);

        $this->activityLog->log('create_maintenance_request', 'maintenance', $maintenance->id, $maintenance->request_number);

        return redirect()->route('tenant.maintenance.show', $maintenance)
            ->with('success', "Maintenance ticket {$maintenance->request_number} filed. Landlord has been notified.");
    }

    public function show(MaintenanceRequest $maintenance): View
    {
        $tenant = auth()->user()->tenantProfile;

        if (!$tenant || $maintenance->tenant_id !== $tenant->id) {
            abort(403, 'Unauthorized.');
        }

        $maintenance->load(['property', 'room', 'bed', 'updates.user']);

        return view('tenant.maintenance.show', compact('tenant', 'maintenance'));
    }
}

