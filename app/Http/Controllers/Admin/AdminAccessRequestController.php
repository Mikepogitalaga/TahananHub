<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HouseOwner;
use App\Models\OwnerAccessRequest;
use App\Services\PrivacyAccessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminAccessRequestController extends Controller
{
    public function __construct(private PrivacyAccessService $privacyService) {}

    public function index(Request $request): View
    {
        $query = OwnerAccessRequest::with(['houseOwner.user', 'requestedBy', 'sessions'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $requests = $query->paginate(15)->withQueryString();

        return view('admin.access-requests.index', compact('requests'));
    }

    public function create(Request $request): View
    {
        $owners = HouseOwner::with('user')->get();
        $selectedOwnerId = $request->input('owner_id');
        $availablePermissions = PrivacyAccessService::PERMISSIONS;

        return view('admin.access-requests.create', compact('owners', 'selectedOwnerId', 'availablePermissions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'house_owner_id'             => ['required', 'exists:house_owners,id'],
            'reason'                     => ['required', 'string', 'min:10', 'max:1000'],
            'requested_duration_minutes' => ['required', 'integer', 'in:15,30,60,240'],
            'permissions'                => ['required', 'array', 'min:1'],
            'permissions.*'              => ['string', 'in:' . implode(',', array_keys(PrivacyAccessService::PERMISSIONS))],
            'request_notes'              => ['nullable', 'string', 'max:500'],
        ]);

        $owner = HouseOwner::findOrFail($validated['house_owner_id']);
        $admin = auth()->user();

        $this->privacyService->requestAccess(
            $admin,
            $owner,
            $validated['permissions'],
            $validated['reason'],
            (int) $validated['requested_duration_minutes'],
            $validated['request_notes'] ?? null
        );

        return redirect()->route('admin.access-requests.index')
            ->with('success', "Temporary access request submitted to {$owner->business_name}. The House Owner must approve it before access is unlocked.");
    }

    public function show(OwnerAccessRequest $accessRequest): View
    {
        $accessRequest->load(['houseOwner.user', 'requestedBy', 'sessions']);
        return view('admin.access-requests.show', compact('accessRequest'));
    }
}

