<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Bed;
use App\Models\Room;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OwnerBedController extends Controller
{
    public function __construct(private ActivityLogService $activityLog) {}

    public function store(Request $request, Room $room): RedirectResponse
    {
        $this->authorizeRoom($room);

        $validated = $request->validate([
            'bed_number'  => ['required', 'string', 'max:50'],
            'rental_rate' => ['nullable', 'numeric', 'min:0'],
            'status'      => ['required', 'in:available,occupied,reserved,maintenance,inactive'],
        ]);

        $bed = $room->beds()->create([
            'bed_number'  => $validated['bed_number'],
            'bed_code'    => "{$room->room_number}-{$validated['bed_number']}",
            'rental_rate' => $validated['rental_rate'] ?? $room->rental_rate,
            'status'      => $validated['status'],
        ]);

        $this->activityLog->log('create_bed', 'beds', $bed->id, "Bed {$bed->bed_number} in Room {$room->room_number}");

        return back()->with('success', "Bed {$bed->bed_number} added.");
    }

    public function updateStatus(Request $request, Bed $bed): RedirectResponse
    {
        $this->authorizeBed($bed);

        $validated = $request->validate([
            'status' => ['required', 'in:available,occupied,reserved,maintenance,inactive'],
        ]);

        // Prevent setting to available if an active tenant is assigned
        if ($validated['status'] === 'available' && $bed->currentAssignment) {
            return back()->with('error', 'Cannot mark bed as available while an active tenant is assigned. Move-out tenant first.');
        }

        $bed->update(['status' => $validated['status']]);

        return back()->with('success', "Bed {$bed->bed_number} status updated to {$validated['status']}.");
    }

    public function destroy(Bed $bed): RedirectResponse
    {
        $this->authorizeBed($bed);

        if ($bed->status === 'occupied' || $bed->currentAssignment) {
            return back()->with('error', 'Cannot delete an occupied bed with an assigned tenant.');
        }

        $room = $bed->room;
        $bed->delete();

        return back()->with('success', "Bed {$bed->bed_number} removed.");
    }

    private function authorizeRoom(Room $room): void
    {
        if ($room->property?->house_owner_id !== auth()->user()->houseOwner?->id) {
            abort(403, 'Unauthorized.');
        }
    }

    private function authorizeBed(Bed $bed): void
    {
        if ($bed->room?->property?->house_owner_id !== auth()->user()->houseOwner?->id) {
            abort(403, 'Unauthorized.');
        }
    }
}

