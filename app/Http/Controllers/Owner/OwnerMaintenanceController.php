<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceRequest;
use App\Models\MaintenanceUpdate;
use App\Services\ActivityLogService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OwnerMaintenanceController extends Controller
{
    public function __construct(
        private ActivityLogService $activityLog
    ) {}

    public function index(Request $request): View
    {
        $owner = auth()->user()->houseOwner;
        $propertyIds = $owner->properties()->pluck('id');

        $query = MaintenanceRequest::whereIn('property_id', $propertyIds)
            ->with(['tenant', 'property', 'room', 'bed'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('property_id')) {
            $query->where('property_id', $request->property_id);
        }

        $requests = $query->paginate(15)->withQueryString();
        $properties = $owner->properties;

        $stats = [
            'pending'     => MaintenanceRequest::whereIn('property_id', $propertyIds)->where('status', 'pending')->count(),
            'in_progress' => MaintenanceRequest::whereIn('property_id', $propertyIds)->where('status', 'in_progress')->count(),
            'resolved'    => MaintenanceRequest::whereIn('property_id', $propertyIds)->where('status', 'resolved')->count(),
        ];

        return view('owner.maintenance.index', compact('requests', 'properties', 'stats'));
    }

    public function show(MaintenanceRequest $maintenance): View
    {
        $this->authorizeOwner($maintenance);
        $maintenance->load(['tenant', 'property', 'room', 'bed', 'updates.user']);

        return view('owner.maintenance.show', compact('maintenance'));
    }

    public function updateStatus(Request $request, MaintenanceRequest $maintenance): RedirectResponse
    {
        $this->authorizeOwner($maintenance);

        $validated = $request->validate([
            'status' => ['required', 'in:pending,acknowledged,in_progress,resolved,cancelled'],
            'note'   => ['required', 'string', 'max:1000'],
        ]);

        $resolvedAt = ($validated['status'] === 'resolved') ? Carbon::now() : $maintenance->resolved_at;

        $maintenance->update([
            'status'      => $validated['status'],
            'resolved_at' => $resolvedAt,
        ]);

        MaintenanceUpdate::create([
            'maintenance_request_id' => $maintenance->id,
            'user_id'                => auth()->id(),
            'status'                 => $validated['status'],
            'note'                   => $validated['note'],
        ]);

        $this->activityLog->log('update_maintenance_status', 'maintenance', $maintenance->id, $maintenance->request_number, [
            'status' => $validated['status'],
        ]);

        return back()->with('success', "Maintenance request updated to " . strtoupper($validated['status']) . ".");
    }

    private function authorizeOwner(MaintenanceRequest $maintenance): void
    {
        $propertyIds = auth()->user()->houseOwner->properties()->pluck('id')->toArray();
        if (!in_array($maintenance->property_id, $propertyIds)) {
            abort(403, 'Unauthorized.');
        }
    }
}

