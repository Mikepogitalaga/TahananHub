<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class OwnerPropertyController extends Controller
{
    public function __construct(private ActivityLogService $activityLog) {}

    public function index(): View
    {
        $owner = auth()->user()->houseOwner;
        $properties = $owner->properties()->withCount(['rooms'])->with('coverPhoto')->latest()->paginate(10);

        return view('owner.properties.index', compact('properties'));
    }

    public function create(): View
    {
        return view('owner.properties.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $owner = auth()->user()->houseOwner;

        $validated = $request->validate([
            'name'               => ['required', 'string', 'max:255'],
            'code'               => ['nullable', 'string', 'max:50', 'unique:properties,code'],
            'address'            => ['required', 'string', 'max:255'],
            'barangay'           => ['required', 'string', 'max:255'],
            'municipality'       => ['required', 'string', 'max:255'],
            'province'           => ['required', 'string', 'max:255'],
            'contact_number'     => ['nullable', 'string', 'max:20'],
            'description'        => ['nullable', 'string', 'max:1000'],
            'status'             => ['required', 'in:active,inactive,under_maintenance'],
            'is_listed'          => ['nullable', 'boolean'],
            'public_description' => ['nullable', 'string', 'max:2000'],
            'public_contact'     => ['nullable', 'string', 'max:50'],
            'amenities_text'     => ['nullable', 'string'],
            'house_rules'        => ['nullable', 'string', 'max:2000'],
            'latitude'           => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'          => ['nullable', 'numeric', 'between:-180,180'],
            'photos'             => ['nullable', 'array', 'max:10'],
            'photos.*'           => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $code = !empty($validated['code'])
            ? strtoupper($validated['code'])
            : 'PROP-' . strtoupper(Str::random(5));

        $amenities = null;
        if (!empty($validated['amenities_text'])) {
            $amenities = array_values(array_filter(
                array_map('trim', explode("\n", $validated['amenities_text']))
            ));
        }

        $property = $owner->properties()->create([
            'name'               => $validated['name'],
            'code'               => $code,
            'address'            => $validated['address'],
            'barangay'           => $validated['barangay'],
            'municipality'       => $validated['municipality'],
            'province'           => $validated['province'],
            'contact_number'     => $validated['contact_number'] ?? null,
            'description'        => $validated['description'] ?? null,
            'status'             => $validated['status'],
            'is_listed'          => $request->boolean('is_listed'),
            'public_description' => $validated['public_description'] ?? null,
            'public_contact'     => $validated['public_contact'] ?? null,
            'amenities'          => $amenities,
            'house_rules'        => $validated['house_rules'] ?? null,
            'latitude'           => $validated['latitude'] ?? null,
            'longitude'          => $validated['longitude'] ?? null,
        ]);

        // Store uploaded photos
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $i => $file) {
                $path = $file->store("properties/{$property->id}/photos", 'public');
                \App\Models\PropertyPhoto::create([
                    'property_id' => $property->id,
                    'path'        => $path,
                    'sort_order'  => $i,
                    'is_cover'    => $i === 0,
                ]);
            }
        }

        $this->activityLog->log('create_property', 'properties', $property->id, $property->name);

        return redirect()->route('owner.properties.index')->with('success', "Property {$property->name} created successfully.");
    }

    public function show(Property $property): View
    {
        $this->authorizeOwner($property);
        $property->load(['rooms.beds.currentAssignment.tenant', 'photos']);
        return view('owner.properties.show', compact('property'));
    }

    public function edit(Property $property): View
    {
        $this->authorizeOwner($property);
        return view('owner.properties.edit', compact('property'));
    }

    public function update(Request $request, Property $property): RedirectResponse
    {
        $this->authorizeOwner($property);

        $validated = $request->validate([
            'name'               => ['required', 'string', 'max:255'],
            'code'               => ['required', 'string', 'max:50', 'unique:properties,code,' . $property->id],
            'address'            => ['required', 'string', 'max:255'],
            'barangay'           => ['required', 'string', 'max:255'],
            'municipality'       => ['required', 'string', 'max:255'],
            'province'           => ['required', 'string', 'max:255'],
            'contact_number'     => ['nullable', 'string', 'max:20'],
            'description'        => ['nullable', 'string', 'max:1000'],
            'status'             => ['required', 'in:active,inactive,under_maintenance'],
            'is_listed'          => ['nullable', 'boolean'],
            'public_description' => ['nullable', 'string', 'max:2000'],
            'public_contact'     => ['nullable', 'string', 'max:50'],
            'amenities_text'     => ['nullable', 'string'],
            'house_rules'        => ['nullable', 'string', 'max:2000'],
            'latitude'           => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'          => ['nullable', 'numeric', 'between:-180,180'],
            'photos'             => ['nullable', 'array', 'max:10'],
            'photos.*'           => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        // Parse amenities from textarea (one per line)
        $amenities = null;
        if (!empty($validated['amenities_text'])) {
            $amenities = array_values(array_filter(
                array_map('trim', explode("\n", $validated['amenities_text']))
            ));
        }

        $property->update([
            'name'               => $validated['name'],
            'code'               => $validated['code'],
            'address'            => $validated['address'],
            'barangay'           => $validated['barangay'],
            'municipality'       => $validated['municipality'],
            'province'           => $validated['province'],
            'contact_number'     => $validated['contact_number'] ?? null,
            'description'        => $validated['description'] ?? null,
            'status'             => $validated['status'],
            'is_listed'          => $request->boolean('is_listed'),
            'public_description' => $validated['public_description'] ?? null,
            'public_contact'     => $validated['public_contact'] ?? null,
            'amenities'          => $amenities,
            'house_rules'        => $validated['house_rules'] ?? null,
            'latitude'           => $validated['latitude'] ?? null,
            'longitude'          => $validated['longitude'] ?? null,
        ]);

        // Append any newly uploaded photos
        if ($request->hasFile('photos')) {
            $isCoverSet = $property->photos()->where('is_cover', true)->exists();
            $sortStart  = $property->photos()->count();
            foreach ($request->file('photos') as $i => $file) {
                $path = $file->store("properties/{$property->id}/photos", 'public');
                \App\Models\PropertyPhoto::create([
                    'property_id' => $property->id,
                    'path'        => $path,
                    'sort_order'  => $sortStart + $i,
                    'is_cover'    => !$isCoverSet && $i === 0,
                ]);
                if (!$isCoverSet && $i === 0) $isCoverSet = true;
            }
        }

        $this->activityLog->log('update_property', 'properties', $property->id, $property->name);

        return redirect()->route('owner.properties.show', $property)->with('success', 'Property updated successfully.');
    }

    public function destroy(Property $property): RedirectResponse
    {
        $this->authorizeOwner($property);

        $name = $property->name;
        $property->delete();

        $this->activityLog->log('delete_property', 'properties', null, $name);

        return redirect()->route('owner.properties.index')->with('success', "Property {$name} deleted.");
    }

    private function authorizeOwner(Property $property): void
    {
        if ($property->house_owner_id !== auth()->user()->houseOwner?->id) {
            abort(403, 'Unauthorized access to this property.');
        }
    }
}
