<?php

namespace App\Http\Middleware;

use App\Models\HouseOwner;
use App\Services\PrivacyAccessService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RequireOwnerPrivacyAccess
{
    public function __construct(private PrivacyAccessService $privacyService) {}

    /**
     * Handle an incoming request for owner protected data.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $permission Required privacy permission key (e.g. VIEW_TENANTS)
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login');
        }

        // 1. House Owners always have full access to their own workspace
        if ($user->isOwner()) {
            return $next($request);
        }

        // 2. Super Admin must possess an active, approved session with the requested permission
        if ($user->isAdmin()) {
            // Identify the target house owner from route parameters
            $ownerId = $request->route('owner')?->id 
                ?? $request->route('owner') 
                ?? $request->input('house_owner_id');

            if ($ownerId) {
                $owner = HouseOwner::find($ownerId);
                if ($owner && $this->privacyService->checkAdminHasAccess($user, $owner, $permission)) {
                    // Log access in Privacy Audit Log
                    $this->privacyService->logProtectedAction(
                        $user,
                        $owner,
                        'view_protected_data',
                        $request->path(),
                        null,
                        "Admin accessed {$permission} under active session"
                    );

                    return $next($request);
                }
            }

            abort(403, "Access Denied: House Owner private business data is protected. You must request and receive temporary permission ({$permission}) from the House Owner.");
        }

        abort(403, 'Unauthorized.');
    }
}

