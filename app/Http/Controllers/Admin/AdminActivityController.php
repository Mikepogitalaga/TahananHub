<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\PrivacyAuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminActivityController extends Controller
{
    public function index(Request $request): View
    {
        $tab = $request->query('tab', 'activity');

        $activityLogs = ActivityLog::with('user')->latest()->paginate(20, ['*'], 'act_page');
        $privacyLogs  = PrivacyAuditLog::with(['adminUser', 'houseOwner.user'])->latest()->paginate(20, ['*'], 'priv_page');

        return view('admin.activity-logs.index', compact('activityLogs', 'privacyLogs', 'tab'));
    }
}

