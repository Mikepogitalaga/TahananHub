<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\PropertyPhoto;
use App\Models\Room;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class OwnerPhotoController extends Controller
{
    // -------------------------------------------------------------------------
    // Property Photos
    // -------------------------------------------------------------------------

    public function storePropertyPhoto(Request $request, Property $property): RedirectResponse
    {
        $this->authorizeProperty($property);

        $request->validate([
            'photos'   => ['required', 'array', 'max:10'],
            'photos.*' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $isCoverSet = $property->photos()->where('is_cover', true)->exists();

        foreach ($request->file('photos') as $i => $file) {
            $path = $file->store("properties/{$property->id}/photos", 'public');
            PropertyPhoto::create([
                'property_id' => $property->id,
                'path'        => $path,
                'sort_order'  => $property->photos()->count() + $i,
                'is_cover'    => !$isCoverSet && $i === 0,
            ]);
            if (!$isCoverSet && $i === 0) {
                $isCoverSet = true;
            }
        }

        return back()->with('success', 'Photos uploaded successfully.');
    }

    public function setCover(Property $property, PropertyPhoto $photo): RedirectResponse
    {
        $this->authorizeProperty($property);
        abort_unless($photo->property_id === $property->id, 403);

        $property->photos()->update(['is_cover' => false]);
        $photo->update(['is_cover' => true]);

        return back()->with('success', 'Cover photo updated.');
    }

    public function destroyPropertyPhoto(Property $property, PropertyPhoto $photo): RedirectResponse
    {
        $this->authorizeProperty($property);
        abort_unless($photo->property_id === $property->id, 403);

        Storage::disk('public')->delete($photo->path);
        $photo->delete();

        // If deleted photo was cover, assign cover to next available
        if ($photo->is_cover) {
            $next = $property->photos()->first();
            $next?->update(['is_cover' => true]);
        }

        return back()->with('success', 'Photo deleted.');
    }

    // -------------------------------------------------------------------------
    // Room Photo
    // -------------------------------------------------------------------------

    public function storeRoomPhoto(Request $request, Room $room): RedirectResponse
    {
        $this->authorizeRoom($room);

        $request->validate([
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        // Delete old photo if exists
        if ($room->photo) {
            Storage::disk('public')->delete($room->photo);
        }

        $path = $request->file('photo')->store("rooms/{$room->id}", 'public');
        $room->update(['photo' => $path]);

        return back()->with('success', 'Room photo updated.');
    }

    public function destroyRoomPhoto(Room $room): RedirectResponse
    {
        $this->authorizeRoom($room);

        if ($room->photo) {
            Storage::disk('public')->delete($room->photo);
            $room->update(['photo' => null]);
        }

        return back()->with('success', 'Room photo removed.');
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function authorizeProperty(Property $property): void
    {
        abort_unless(
            $property->house_owner_id === Auth::user()->houseOwner?->id,
            403
        );
    }

    private function authorizeRoom(Room $room): void
    {
        $property = $room->property;
        abort_unless(
            $property->house_owner_id === Auth::user()->houseOwner?->id,
            403
        );
    }
}
