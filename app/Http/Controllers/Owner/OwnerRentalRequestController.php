<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Bed;
use App\Models\RentalRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OwnerRentalRequestController extends Controller
{
    private function ownerHouseOwnerId(): int
    {
        return Auth::user()->houseOwner->id;
    }

    public function index(): View
    {
        $requests = RentalRequest::where('house_owner_id', $this->ownerHouseOwnerId())
            ->with(['user', 'property', 'room', 'bed'])
            ->latest()
            ->paginate(20);

        return view('owner.rental-requests.index', compact('requests'));
    }

    public function show(RentalRequest $rentalRequest): View
    {
        abort_unless($rentalRequest->house_owner_id === $this->ownerHouseOwnerId(), 403);
        $rentalRequest->load(['user', 'property', 'room', 'bed']);
        return view('owner.rental-requests.show', compact('rentalRequest'));
    }

    public function approve(Request $request, RentalRequest $rentalRequest): RedirectResponse
    {
        abort_unless($rentalRequest->house_owner_id === $this->ownerHouseOwnerId(), 403);
        abort_unless($rentalRequest->isPending(), 422);

        $validated = $request->validate([
            'owner_response' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($rentalRequest, $validated) {
            // Re-check bed availability inside the transaction with a lock
            $bed = Bed::lockForUpdate()->findOrFail($rentalRequest->bed_id);

            if ($bed->status !== 'available') {
                throw new \RuntimeException('This bed is no longer available.');
            }

            $bed->update(['status' => 'reserved']);

            $rentalRequest->update([
                'status'         => 'approved',
                'owner_response' => $validated['owner_response'] ?? null,
                'responded_at'   => now(),
            ]);
        });

        return redirect()->route('owner.rental-requests.show', $rentalRequest)
            ->with('success', 'Request approved and bed reserved.');
    }

    public function reject(Request $request, RentalRequest $rentalRequest): RedirectResponse
    {
        abort_unless($rentalRequest->house_owner_id === $this->ownerHouseOwnerId(), 403);
        abort_unless($rentalRequest->isPending(), 422);

        $validated = $request->validate([
            'owner_response' => ['nullable', 'string', 'max:1000'],
        ]);

        $rentalRequest->update([
            'status'         => 'rejected',
            'owner_response' => $validated['owner_response'] ?? null,
            'responded_at'   => now(),
        ]);

        return redirect()->route('owner.rental-requests.show', $rentalRequest)
            ->with('success', 'Request rejected.');
    }
}
