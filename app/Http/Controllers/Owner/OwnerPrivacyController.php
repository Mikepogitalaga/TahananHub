<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\OwnerAccessRequest;
use App\Models\OwnerAccessSession;
use App\Models\PrivacyAuditLog;
use App\Services\ActivityLogService;
use App\Services\PrivacyAccessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OwnerPrivacyController extends Controller
{
    public function __construct(
        private PrivacyAccessService $privacyService,
        private ActivityLogService $activityLog
    ) {}

    public function index(): View
    {
        $owner = auth()->user()->houseOwner;

        $pendingRequests = OwnerAccessRequest::where('house_owner_id', $owner->id)
            ->where('status', 'pending')
            ->with('requestedBy')
            ->latest()
            ->get();

        $activeSessions = OwnerAccessSession::where('house_owner_id', $owner->id)
            ->where('is_active', true)
            ->where('expires_at', '>', now())
            ->with(['admin', 'accessRequest'])
            ->latest('granted_at')
            ->get();

        $requestHistory = OwnerAccessRequest::where('house_owner_id', $owner->id)
            ->whereIn('status', ['approved', 'denied', 'expired', 'revoked'])
            ->with('requestedBy')
            ->latest()
            ->take(10)
            ->get();

        $auditLogs = PrivacyAuditLog::where('house_owner_id', $owner->id)
            ->with('adminUser')
            ->latest()
            ->paginate(15);

        $permissionsMap = PrivacyAccessService::PERMISSIONS;

        return view('owner.privacy.index', compact(
            'owner',
            'pendingRequests',
            'activeSessions',
            'requestHistory',
            'auditLogs',
            'permissionsMap'
        ));
    }

    public function respond(Request $request, OwnerAccessRequest $accessRequest): RedirectResponse
    {
        $owner = auth()->user()->houseOwner;
        if ($accessRequest->house_owner_id !== $owner->id) {
            abort(403, 'Unauthorized.');
        }

        $validated = $request->validate([
            'action' => ['required', 'in:approve,deny'],
            'notes'  => ['nullable', 'string', 'max:500'],
        ]);

        if ($validated['action'] === 'approve') {
            $session = $this->privacyService->approveAccess($accessRequest, auth()->user(), $validated['notes'] ?? null);
            $this->activityLog->log('approve_admin_access', 'privacy', $session->id, "Approved session until {$session->expires_at}");
            return redirect()->route('owner.privacy.index')->with('success', "Access granted to Platform Admin until " . $session->expires_at->format('M d, Y h:i A') . ".");
        } else {
            $this->privacyService->denyAccess($accessRequest, auth()->user(), $validated['notes'] ?? null);
            $this->activityLog->log('deny_admin_access', 'privacy', $accessRequest->id, "Denied request #{$accessRequest->id}");
            return redirect()->route('owner.privacy.index')->with('info', "Access request denied.");
        }
    }

    public function revoke(OwnerAccessSession $session): RedirectResponse
    {
        $owner = auth()->user()->houseOwner;
        if ($session->house_owner_id !== $owner->id) {
            abort(403, 'Unauthorized.');
        }

        $this->privacyService->revokeAccess($session, auth()->user());
        $this->activityLog->log('revoke_admin_access', 'privacy', $session->id, "Revoked session #{$session->id}");

        return redirect()->route('owner.privacy.index')->with('success', "Active admin session immediately revoked. All access terminated.");
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $owner = auth()->user()->houseOwner;

        $owner->update([
            'privacy_require_approval' => $request->boolean('privacy_require_approval', true),
            'privacy_notify_on_access' => $request->boolean('privacy_notify_on_access', true),
        ]);

        $this->activityLog->log('update_privacy_settings', 'privacy', $owner->id, "Updated privacy boundaries");

        return redirect()->route('owner.privacy.index')->with('success', "Privacy preferences updated.");
    }
}

