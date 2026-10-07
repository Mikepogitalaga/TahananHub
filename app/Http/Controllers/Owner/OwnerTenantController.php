<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Bed;
use App\Models\Property;
use App\Models\Room;
use App\Models\Tenant;
use App\Models\TenantAssignment;
use App\Models\User;
use App\Services\ActivityLogService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class OwnerTenantController extends Controller
{
    public function __construct(private ActivityLogService $activityLog) {}

    public function index(Request $request): View
    {
        $owner = auth()->user()->houseOwner;
        $query = $owner->tenants()->with(['currentAssignment.property', 'currentAssignment.room', 'currentAssignment.bed'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('contact_number', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $tenants = $query->paginate(15)->withQueryString();

        return view('owner.tenants.index', compact('tenants'));
    }

    public function create(): View
    {
        $owner = auth()->user()->houseOwner;
        $properties = $owner->properties()->with(['rooms.beds' => function ($q) {
            $q->where('status', 'available');
        }])->get();

        return view('owner.tenants.create', compact('properties'));
    }

    public function store(Request $request): RedirectResponse
    {
        $owner = auth()->user()->houseOwner;

        $validated = $request->validate([
            'first_name'               => ['required', 'string', 'max:100'],
            'middle_name'              => ['nullable', 'string', 'max:100'],
            'last_name'                => ['required', 'string', 'max:100'],
            'suffix'                   => ['nullable', 'string', 'max:20'],
            'date_of_birth'            => ['nullable', 'date'],
            'gender'                   => ['nullable', 'in:male,female,other'],
            'contact_number'           => ['nullable', 'string', 'max:20'],
            'email'                    => ['nullable', 'email', 'max:255'],
            'address'                  => ['nullable', 'string', 'max:500'],
            'emergency_contact'        => ['nullable', 'string', 'max:100'],
            'emergency_relationship'   => ['nullable', 'string', 'max:50'],
            'emergency_contact_number' => ['nullable', 'string', 'max:20'],
            'gov_id_type'              => ['nullable', 'string', 'max:50'],
            'gov_id_number'            => ['nullable', 'string', 'max:100'],
            'create_user_account'      => ['nullable', 'boolean'],
            'password'                 => ['nullable', 'string', 'min:8'],
            // Optional direct bed assignment upon creation
            'bed_id'                   => ['nullable', 'exists:beds,id'],
        ]);

        $tenant = DB::transaction(function () use ($owner, $validated, $request) {
            $userId = null;

            // Optionally create login account for tenant portal
            if ($request->boolean('create_user_account') && !empty($validated['email'])) {
                $user = User::create([
                    'name'     => trim("{$validated['first_name']} {$validated['last_name']}"),
                    'email'    => $validated['email'],
                    'phone'    => $validated['contact_number'] ?? null,
                    'password' => Hash::make($validated['password'] ?? 'tenant1234'),
                    'role'     => User::ROLE_TENANT,
                    'status'   => 'active',
                ]);
                $userId = $user->id;
            }

            $tenantData = array_merge($validated, [
                'user_id'                   => $userId,
                'gov_id_number_encrypted'   => !empty($validated['gov_id_number']) ? encrypt($validated['gov_id_number']) : null,
                'status'                    => 'active',
            ]);

            $tenant = $owner->tenants()->create($tenantData);

            // Assign bed if selected
            if (!empty($validated['bed_id'])) {
                $bed = Bed::with('room.property')->findOrFail($validated['bed_id']);
                if ($bed->status === 'available') {
                    TenantAssignment::create([
                        'tenant_id'   => $tenant->id,
                        'property_id' => $bed->room->property_id,
                        'room_id'     => $bed->room_id,
                        'bed_id'      => $bed->id,
                        'assigned_at' => Carbon::now(),
                        'is_current'  => true,
                    ]);

                    $bed->update(['status' => 'occupied']);
                }
            }

            return $tenant;
        });

        $this->activityLog->log('create_tenant', 'tenants', $tenant->id, $tenant->full_name);

        return redirect()->route('owner.tenants.show', $tenant)->with('success', "Tenant {$tenant->full_name} registered successfully.");
    }

    public function show(Tenant $tenant): View
    {
        $this->authorizeOwner($tenant);

        $tenant->load([
            'currentAssignment.property',
            'currentAssignment.room',
            'currentAssignment.bed',
            'tenantAssignments.property',
            'tenantAssignments.room',
            'tenantAssignments.bed',
            'rentalContracts.property',
            'rentBillings.payments',
            'maintenanceRequests',
        ]);

        $owner = auth()->user()->houseOwner;
        $availableProperties = $owner->properties()->with(['rooms.beds' => function ($q) {
            $q->where('status', 'available');
        }])->get();

        return view('owner.tenants.show', compact('tenant', 'availableProperties'));
    }

    public function edit(Tenant $tenant): View
    {
        $this->authorizeOwner($tenant);
        return view('owner.tenants.edit', compact('tenant'));
    }

    public function update(Request $request, Tenant $tenant): RedirectResponse
    {
        $this->authorizeOwner($tenant);

        $validated = $request->validate([
            'first_name'               => ['required', 'string', 'max:100'],
            'middle_name'              => ['nullable', 'string', 'max:100'],
            'last_name'                => ['required', 'string', 'max:100'],
            'suffix'                   => ['nullable', 'string', 'max:20'],
            'date_of_birth'            => ['nullable', 'date'],
            'gender'                   => ['nullable', 'in:male,female,other'],
            'contact_number'           => ['nullable', 'string', 'max:20'],
            'email'                    => ['nullable', 'email', 'max:255'],
            'address'                  => ['nullable', 'string', 'max:500'],
            'emergency_contact'        => ['nullable', 'string', 'max:100'],
            'emergency_relationship'   => ['nullable', 'string', 'max:50'],
            'emergency_contact_number' => ['nullable', 'string', 'max:20'],
            'gov_id_type'              => ['nullable', 'string', 'max:50'],
            'gov_id_number'            => ['nullable', 'string', 'max:100'],
            'status'                   => ['required', 'in:active,inactive,moved_out,suspended'],
        ]);

        if (!empty($validated['gov_id_number'])) {
            $validated['gov_id_number_encrypted'] = encrypt($validated['gov_id_number']);
        }
        unset($validated['gov_id_number']);

        $tenant->update($validated);

        $this->activityLog->log('update_tenant', 'tenants', $tenant->id, $tenant->full_name);

        return redirect()->route('owner.tenants.show', $tenant)->with('success', 'Tenant details updated.');
    }

    /**
     * Assign tenant to a new bed.
     */
    public function assignBed(Request $request, Tenant $tenant): RedirectResponse
    {
        $this->authorizeOwner($tenant);

        $validated = $request->validate([
            'bed_id' => ['required', 'exists:beds,id'],
        ]);

        $bed = Bed::with('room.property')->findOrFail($validated['bed_id']);

        if ($bed->status !== 'available') {
            return back()->with('error', 'The selected bed is no longer available.');
        }

        DB::transaction(function () use ($tenant, $bed) {
            // Vacate previous bed if any
            if ($tenant->currentAssignment) {
                $oldAssignment = $tenant->currentAssignment;
                $oldAssignment->update([
                    'vacated_at' => Carbon::now(),
                    'is_current' => false,
                ]);
                $oldAssignment->bed?->update(['status' => 'available']);
            }

            // Create new assignment
            TenantAssignment::create([
                'tenant_id'   => $tenant->id,
                'property_id' => $bed->room->property_id,
                'room_id'     => $bed->room_id,
                'bed_id'      => $bed->id,
                'assigned_at' => Carbon::now(),
                'is_current'  => true,
            ]);

            $bed->update(['status' => 'occupied']);
            $tenant->update(['status' => 'active']);
        });

        $this->activityLog->log('assign_bed', 'tenants', $tenant->id, "Assigned to {$bed->room->property->name} - Bed {$bed->bed_number}");

        return back()->with('success', "Tenant assigned to Bed {$bed->bed_number} successfully.");
    }

    /**
     * Move out tenant (frees bed, preserves historical record).
     */
    public function moveOut(Tenant $tenant): RedirectResponse
    {
        $this->authorizeOwner($tenant);

        DB::transaction(function () use ($tenant) {
            if ($tenant->currentAssignment) {
                $assignment = $tenant->currentAssignment;
                $assignment->update([
                    'vacated_at' => Carbon::now(),
                    'is_current' => false,
                ]);

                $assignment->bed?->update(['status' => 'available']);
            }

            // Mark active contracts terminated
            $tenant->rentalContracts()->where('status', 'active')->update(['status' => 'terminated']);

            $tenant->update(['status' => 'moved_out']);
        });

        $this->activityLog->log('move_out', 'tenants', $tenant->id, "{$tenant->full_name} moved out");

        return back()->with('success', "Tenant {$tenant->full_name} marked as moved out. Bed is now available.");
    }

    public function destroy(Tenant $tenant): RedirectResponse
    {
        $this->authorizeOwner($tenant);

        if ($tenant->currentAssignment) {
            return back()->with('error', 'Cannot delete an active tenant currently assigned to a bed. Move out first.');
        }

        $name = $tenant->full_name;
        $tenant->delete();

        $this->activityLog->log('delete_tenant', 'tenants', null, $name);

        return redirect()->route('owner.tenants.index')->with('success', "Tenant {$name} record deleted.");
    }

    private function authorizeOwner(Tenant $tenant): void
    {
        if ($tenant->house_owner_id !== auth()->user()->houseOwner?->id) {
            abort(403, 'Unauthorized access to this tenant.');
        }
    }
}

