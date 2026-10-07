<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ActivityLogService
{
    /**
     * Record an activity log entry.
     */
    public function log(
        string $action,
        string $module,
        ?int $recordId = null,
        ?string $recordLabel = null,
        ?array $changes = null
    ): ActivityLog {
        $user = Auth::user();

        return ActivityLog::create([
            'user_id'      => $user?->id,
            'user_name'    => $user?->name ?? 'System/Guest',
            'action'       => $action,
            'module'       => $module,
            'record_id'    => $recordId,
            'record_label' => $recordLabel,
            'changes'      => $changes,
            'ip_address'   => Request::ip(),
        ]);
    }
}

