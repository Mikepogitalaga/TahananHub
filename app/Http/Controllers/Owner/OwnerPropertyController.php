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
        $properties = $owner->properties()->withCount(['rooms'])->latest()->paginate(10);

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
            'name'           => ['required', 'string', 'max:255'],
            'code'           => ['nullable', 'string', 'max:50', 'unique:properties,code'],
            'address'        => ['required', 'string', 'max:255'],
            'barangay'       => ['required', 'string', 'max:255'],
            'municipality'   => ['required', 'string', 'max:255'],
            'province'       => ['required', 'string', 'max:255'],
            'contact_number' => ['nullable', 'string', 'max:20'],
            'description'    => ['nullable', 'string', 'max:1000'],
            'status'         => ['required', 'in:active,inactive,under_maintenance'],
        ]);

        $code = !empty($validated['code']) 
            ? strtoupper($validated['code']) 
            : 'PROP-' . strtoupper(Str::random(5));

        $property = $owner->properties()->create(array_merge($validated, ['code' => $code]));

        $this->activityLog->log('create_property', 'properties', $property->id, $property->name);

        return redirect()->route('owner.properties.index')->with('success', "Property {$property->name} created successfully.");
    }

    public function show(Property $property): View
    {
        $this->authorizeOwner($property);

        $property->load(['rooms.beds.currentAssignment.tenant']);
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
            'name'           => ['required', 'string', 'max:255'],
            'code'           => ['required', 'string', 'max:50', 'unique:properties,code,' . $property->id],
            'address'        => ['required', 'string', 'max:255'],
            'barangay'       => ['required', 'string', 'max:255'],
            'municipality'   => ['required', 'string', 'max:255'],
            'province'       => ['required', 'string', 'max:255'],
            'contact_number' => ['nullable', 'string', 'max:20'],
            'description'    => ['nullable', 'string', 'max:1000'],
            'status'         => ['required', 'in:active,inactive,under_maintenance'],
        ]);

        $property->update($validated);

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

