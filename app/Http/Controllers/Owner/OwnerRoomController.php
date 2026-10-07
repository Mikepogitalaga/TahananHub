<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Bed;
use App\Models\Property;
use App\Models\Room;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OwnerRoomController extends Controller
{
    public function __construct(private ActivityLogService $activityLog) {}

    public function index(Request $request): View
    {
        $owner = auth()->user()->houseOwner;
        $properties = $owner->properties()->get();

        $query = Room::whereIn('property_id', $properties->pluck('id'))->with(['property', 'beds.currentAssignment.tenant']);

        if ($request->filled('property_id')) {
            $query->where('property_id', $request->property_id);
        }

        $rooms = $query->paginate(12)->withQueryString();

        return view('owner.rooms.index', compact('rooms', 'properties'));
    }

    public function create(Request $request): View
    {
        $owner = auth()->user()->houseOwner;
        $properties = $owner->properties()->get();
        $selectedPropertyId = $request->input('property_id');

        return view('owner.rooms.create', compact('properties', 'selectedPropertyId'));
    }

    public function store(Request $request): RedirectResponse
    {
        $owner = auth()->user()->houseOwner;

        $validated = $request->validate([
            'property_id'   => ['required', 'exists:properties,id'],
            'room_number'   => ['required', 'string', 'max:50'],
            'room_name'     => ['nullable', 'string', 'max:100'],
            'floor'         => ['nullable', 'string', 'max:50'],
            'room_type'     => ['required', 'in:single,double,shared,dormitory,other'],
            'capacity'      => ['required', 'integer', 'min:1', 'max:50'],
            'rental_rate'   => ['required', 'numeric', 'min:0'],
            'description'   => ['nullable', 'string', 'max:1000'],
            'status'        => ['required', 'in:available,full,maintenance,inactive'],
            'auto_generate_beds' => ['nullable', 'boolean'],
        ]);

        // Security check
        $property = Property::findOrFail($validated['property_id']);
        if ($property->house_owner_id !== $owner->id) {
            abort(403, 'Unauthorized.');
        }

        $room = DB::transaction(function () use ($validated, $request) {
            $room = Room::create($validated);

            // Automatically generate beds if requested
            if ($request->boolean('auto_generate_beds', true)) {
                $capacity = (int) $validated['capacity'];
                $letters = range('A', 'Z');
                for ($i = 0; $i < $capacity; $i++) {
                    $bedNumber = $capacity > 1 ? ($letters[$i] ?? (string)($i + 1)) : '1';
                    Bed::create([
                        'room_id'     => $room->id,
                        'bed_number'  => $bedNumber,
                        'bed_code'    => "{$room->room_number}-{$bedNumber}",
                        'rental_rate' => $room->rental_rate,
                        'status'      => 'available',
                    ]);
                }
            }

            return $room;
        });

        $this->activityLog->log('create_room', 'rooms', $room->id, "Room {$room->room_number}");

        return redirect()->route('owner.properties.show', $property)->with('success', "Room {$room->room_number} added with beds.");
    }

    public function show(Room $room): View
    {
        $this->authorizeOwner($room);
        $room->load(['property', 'beds.currentAssignment.tenant']);

        return view('owner.rooms.show', compact('room'));
    }

    public function edit(Room $room): View
    {
        $this->authorizeOwner($room);
        $properties = auth()->user()->houseOwner->properties()->get();

        return view('owner.rooms.edit', compact('room', 'properties'));
    }

    public function update(Request $request, Room $room): RedirectResponse
    {
        $this->authorizeOwner($room);

        $validated = $request->validate([
            'room_number' => ['required', 'string', 'max:50'],
            'room_name'   => ['nullable', 'string', 'max:100'],
            'floor'       => ['nullable', 'string', 'max:50'],
            'room_type'   => ['required', 'in:single,double,shared,dormitory,other'],
            'capacity'    => ['required', 'integer', 'min:1'],
            'rental_rate' => ['required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string', 'max:1000'],
            'status'      => ['required', 'in:available,full,maintenance,inactive'],
        ]);

        $room->update($validated);

        $this->activityLog->log('update_room', 'rooms', $room->id, "Room {$room->room_number}");

        return redirect()->route('owner.rooms.show', $room)->with('success', 'Room updated successfully.');
    }

    public function destroy(Room $room): RedirectResponse
    {
        $this->authorizeOwner($room);
        $property = $room->property;

        $room->delete();

        $this->activityLog->log('delete_room', 'rooms', null, "Room {$room->room_number}");

        return redirect()->route('owner.properties.show', $property)->with('success', 'Room deleted.');
    }

    private function authorizeOwner(Room $room): void
    {
        if ($room->property?->house_owner_id !== auth()->user()->houseOwner?->id) {
            abort(403, 'Unauthorized access to this room.');
        }
    }
}

