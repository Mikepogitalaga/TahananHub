<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Property;
use App\Services\ActivityLogService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OwnerAnnouncementController extends Controller
{
    public function __construct(
        private ActivityLogService $activityLog
    ) {}

    public function index(Request $request): View
    {
        $owner = auth()->user()->houseOwner;

        $query = Announcement::where('house_owner_id', $owner->id)
            ->with('property')
            ->latest();

        if ($request->filled('property_id')) {
            $query->where('property_id', $request->property_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $announcements = $query->paginate(15)->withQueryString();
        $properties = $owner->properties;

        return view('owner.announcements.index', compact('announcements', 'properties'));
    }

    public function create(): View
    {
        $owner = auth()->user()->houseOwner;
        $properties = $owner->properties;

        return view('owner.announcements.create', compact('properties'));
    }

    public function store(Request $request): RedirectResponse
    {
        $owner = auth()->user()->houseOwner;

        $validated = $request->validate([
            'property_id' => ['nullable', 'exists:properties,id'],
            'title'       => ['required', 'string', 'max:255'],
            'content'     => ['required', 'string'],
            'status'      => ['required', 'in:active,draft,inactive'],
            'publish_at'  => ['nullable', 'date'],
            'expires_at'  => ['nullable', 'date', 'after_or_equal:publish_at'],
        ]);

        if (!empty($validated['property_id'])) {
            Property::where('house_owner_id', $owner->id)->findOrFail($validated['property_id']);
        }

        $announcement = Announcement::create([
            'house_owner_id'   => $owner->id,
            'property_id'      => $validated['property_id'] ?: null,
            'title'            => $validated['title'],
            'content'          => $validated['content'],
            'status'           => $validated['status'],
            'is_platform_wide' => false,
            'publish_at'       => $validated['publish_at'] ?? Carbon::now(),
            'expires_at'       => $validated['expires_at'] ?? null,
        ]);

        $this->activityLog->log('create_announcement', 'announcement', $announcement->id, $announcement->title);

        return redirect()->route('owner.announcements.index')->with('success', "Notice '{$announcement->title}' published.");
    }

    public function edit(Announcement $announcement): View
    {
        $this->authorizeOwner($announcement);
        $owner = auth()->user()->houseOwner;
        $properties = $owner->properties;

        return view('owner.announcements.edit', compact('announcement', 'properties'));
    }

    public function update(Request $request, Announcement $announcement): RedirectResponse
    {
        $this->authorizeOwner($announcement);
        $owner = auth()->user()->houseOwner;

        $validated = $request->validate([
            'property_id' => ['nullable', 'exists:properties,id'],
            'title'       => ['required', 'string', 'max:255'],
            'content'     => ['required', 'string'],
            'status'      => ['required', 'in:active,draft,inactive'],
            'publish_at'  => ['nullable', 'date'],
            'expires_at'  => ['nullable', 'date', 'after_or_equal:publish_at'],
        ]);

        if (!empty($validated['property_id'])) {
            Property::where('house_owner_id', $owner->id)->findOrFail($validated['property_id']);
        }

        $announcement->update([
            'property_id' => $validated['property_id'] ?: null,
            'title'       => $validated['title'],
            'content'     => $validated['content'],
            'status'      => $validated['status'],
            'publish_at'  => $validated['publish_at'] ?? $announcement->publish_at,
            'expires_at'  => $validated['expires_at'] ?? null,
        ]);

        $this->activityLog->log('update_announcement', 'announcement', $announcement->id, $announcement->title);

        return redirect()->route('owner.announcements.index')->with('success', "Announcement updated.");
    }

    public function destroy(Announcement $announcement): RedirectResponse
    {
        $this->authorizeOwner($announcement);
        $title = $announcement->title;
        $announcement->delete();

        $this->activityLog->log('delete_announcement', 'announcement', null, $title);

        return redirect()->route('owner.announcements.index')->with('success', "Notice deleted.");
    }

    private function authorizeOwner(Announcement $announcement): void
    {
        if ($announcement->house_owner_id !== auth()->user()->houseOwner?->id) {
            abort(403, 'Unauthorized.');
        }
    }
}

