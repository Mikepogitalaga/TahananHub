<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HouseController extends Controller
{
    public function index(Request $request): View
    {
        $query = Property::publiclyListed()->with(['photos', 'rooms.beds']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('barangay', 'like', "%{$search}%")
                  ->orWhere('municipality', 'like', "%{$search}%")
                  ->orWhere('province', 'like', "%{$search}%")
                  ->orWhere('public_description', 'like', "%{$search}%");
            });
        }

        if ($municipality = $request->input('municipality')) {
            $query->where('municipality', $municipality);
        }

        if ($barangay = $request->input('barangay')) {
            $query->where('barangay', $barangay);
        }

        if ($minPrice = $request->input('min_price')) {
            $query->whereHas('rooms.beds', fn ($q) => $q->where('rental_rate', '>=', $minPrice));
        }

        if ($maxPrice = $request->input('max_price')) {
            $query->whereHas('rooms.beds', fn ($q) => $q->where('rental_rate', '<=', $maxPrice));
        }

        if ($roomType = $request->input('room_type')) {
            $query->whereHas('rooms', fn ($q) => $q->where('room_type', $roomType));
        }

        if ($request->boolean('available_only', true)) {
            $query->whereHas('rooms.beds', fn ($q) => $q->where('status', 'available'));
        }

        $properties    = $query->paginate(12)->withQueryString();
        $municipalities = Property::publiclyListed()->distinct()->pluck('municipality')->filter()->sort()->values();

        return view('public.houses.index', compact('properties', 'municipalities'));
    }

    public function show(Property $property): View
    {
        abort_unless($property->status === 'active' && $property->is_listed, 404);

        $property->load(['photos', 'rooms' => function ($q) {
            $q->where('status', '!=', 'inactive')->with(['beds' => function ($b) {
                $b->whereIn('status', ['available', 'occupied', 'reserved', 'maintenance']);
            }]);
        }]);

        return view('public.houses.show', compact('property'));
    }

    public function room(Property $property, Room $room): View
    {
        abort_unless($property->status === 'active' && $property->is_listed, 404);
        abort_unless($room->property_id === $property->id, 404);

        $room->load(['beds' => function ($q) {
            $q->whereIn('status', ['available', 'occupied', 'reserved', 'maintenance']);
        }]);

        return view('public.houses.room', compact('property', 'room'));
    }
}
