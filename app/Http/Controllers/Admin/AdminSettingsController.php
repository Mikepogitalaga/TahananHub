<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminSettingsController extends Controller
{
    public function __construct(private ActivityLogService $activityLog) {}

    public function index(): View
    {
        $settings = Setting::pluck('value', 'key')->toArray();
        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $inputs = $request->except(['_token']);

        foreach ($inputs as $key => $value) {
            Setting::set($key, (string) $value);
        }

        $this->activityLog->log('update_settings', 'settings', null, 'Platform Settings Updated');

        return redirect()->route('admin.settings.index')->with('success', 'Platform settings saved successfully.');
    }
}

