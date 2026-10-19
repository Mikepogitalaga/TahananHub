<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Bed;
use App\Models\Property;
use App\Models\RentalRequest;
use App\Models\Room;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

use Illuminate\Support\Facades\Gate;

class TenantRentalRequestController extends Controller
{
    public function index(): View
    {
        $requests = RentalRequest::where('user_id', Auth::id())
            ->with(['property', 'room', 'bed'])
            ->latest()
            ->paginate(15);

        return view('tenant.rental-requests.index', compact('requests'));
    }

    public function show(RentalRequest $rentalRequest): View
    {
        Gate::authorize('view', $rentalRequest);
        $rentalRequest->load(['property', 'room', 'bed']);
        return view('tenant.rental-requests.show', compact('rentalRequest'));
    }

    public function create(Request $request): View|RedirectResponse
    {
        $property = Property::publiclyListed()->findOrFail($request->input('property'));
        $room     = Room::where('property_id', $property->id)->findOrFail($request->input('room'));
        $bed      = Bed::where('room_id', $room->id)
                       ->where('status', 'available')
                       ->findOrFail($request->input('bed'));

        // Check tenant has no active pending request for this bed
        $existing = RentalRequest::where('user_id', Auth::id())
            ->where('bed_id', $bed->id)
            ->where('status', 'pending')
            ->exists();

        if ($existing) {
            return redirect()->route('houses.room', [$property, $room])
                ->with('error', 'You already have a pending request for this bed.');
        }

        return view('tenant.rental-requests.create', compact('property', 'room', 'bed'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'property_id'          => ['required', 'integer'],
            'room_id'              => ['required', 'integer'],
            'bed_id'               => ['required', 'integer'],
            'desired_move_in_date' => ['required', 'date', 'after_or_equal:today'],
            'message'              => ['nullable', 'string', 'max:1000'],
        ]);

        // Server-side re-fetch — never trust client-supplied price
        $property = Property::publiclyListed()->findOrFail($validated['property_id']);
        $room     = Room::where('property_id', $property->id)->findOrFail($validated['room_id']);
        $bed      = Bed::where('room_id', $room->id)->findOrFail($validated['bed_id']);

        if ($bed->status !== 'available') {
            return back()->with('error', 'Sorry, that bed is no longer available.');
        }

        $existing = RentalRequest::where('user_id', Auth::id())
            ->where('bed_id', $bed->id)
            ->where('status', 'pending')
            ->exists();

        if ($existing) {
            return back()->with('error', 'You already have a pending request for this bed.');
        }

        $rentalRequest = RentalRequest::create([
            'request_number'       => RentalRequest::generateNumber(),
            'user_id'              => Auth::id(),
            'property_id'          => $property->id,
            'room_id'              => $room->id,
            'bed_id'               => $bed->id,
            'house_owner_id'       => $property->house_owner_id,
            'monthly_rent'         => $bed->rental_rate,
            'desired_move_in_date' => $validated['desired_move_in_date'],
            'message'              => $validated['message'] ?? null,
            'status'               => 'pending',
        ]);

        return redirect()->route('tenant.rental-requests.show', $rentalRequest)
            ->with('success', 'Your rental request has been submitted! The owner will review it shortly.');
    }

    public function cancel(RentalRequest $rentalRequest): RedirectResponse
    {
        Gate::authorize('cancel', $rentalRequest);
        $rentalRequest->update(['status' => 'withdrawn']);
        return back()->with('success', 'Your rental request has been withdrawn.');
    }
}
