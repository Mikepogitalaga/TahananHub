<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\RentalContract;
use App\Models\Tenant;
use App\Services\ActivityLogService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class OwnerContractController extends Controller
{
    public function __construct(private ActivityLogService $activityLog) {}

    public function index(Request $request): View
    {
        $owner = auth()->user()->houseOwner;
        $propertyIds = $owner->properties()->pluck('id');

        $query = RentalContract::whereIn('property_id', $propertyIds)
            ->with(['tenant', 'property', 'room', 'bed'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $contracts = $query->paginate(12)->withQueryString();

        return view('owner.contracts.index', compact('contracts'));
    }

    public function create(Request $request): View
    {
        $owner = auth()->user()->houseOwner;
        $tenants = $owner->tenants()->where('status', 'active')->with('currentAssignment')->get();
        $properties = $owner->properties()->with('rooms.beds')->get();
        $selectedTenantId = $request->input('tenant_id');

        return view('owner.contracts.create', compact('tenants', 'properties', 'selectedTenantId'));
    }

    public function store(Request $request): RedirectResponse
    {
        $owner = auth()->user()->houseOwner;

        $validated = $request->validate([
            'tenant_id'        => ['required', 'exists:tenants,id'],
            'property_id'      => ['required', 'exists:properties,id'],
            'room_id'          => ['required', 'exists:rooms,id'],
            'bed_id'           => ['required', 'exists:beds,id'],
            'start_date'       => ['required', 'date'],
            'end_date'         => ['nullable', 'date', 'after_or_equal:start_date'],
            'monthly_rent'     => ['required', 'numeric', 'min:0'],
            'security_deposit' => ['nullable', 'numeric', 'min:0'],
            'advance_payment'  => ['nullable', 'numeric', 'min:0'],
            'due_day'          => ['required', 'integer', 'min:1', 'max:31'],
            'late_fee'         => ['nullable', 'numeric', 'min:0'],
            'terms'            => ['nullable', 'string', 'max:2000'],
            'status'           => ['required', 'in:active,pending,expired,terminated'],
        ]);

        $contractNumber = 'CTR-' . date('Y') . '-' . strtoupper(Str::random(6));

        $contract = RentalContract::create(array_merge($validated, [
            'contract_number'  => $contractNumber,
            'security_deposit' => $validated['security_deposit'] ?? 0,
            'advance_payment'  => $validated['advance_payment'] ?? 0,
            'late_fee'         => $validated['late_fee'] ?? 0,
        ]));

        $this->activityLog->log('create_contract', 'contracts', $contract->id, $contract->contract_number);

        return redirect()->route('owner.contracts.show', $contract)->with('success', "Rental contract {$contract->contract_number} created.");
    }

    public function show(RentalContract $contract): View
    {
        $this->authorizeOwner($contract);
        $contract->load(['tenant', 'property', 'room', 'bed', 'rentBillings.payments']);

        return view('owner.contracts.show', compact('contract'));
    }

    public function edit(RentalContract $contract): View
    {
        $this->authorizeOwner($contract);
        return view('owner.contracts.edit', compact('contract'));
    }

    public function update(Request $request, RentalContract $contract): RedirectResponse
    {
        $this->authorizeOwner($contract);

        $validated = $request->validate([
            'start_date'       => ['required', 'date'],
            'end_date'         => ['nullable', 'date'],
            'monthly_rent'     => ['required', 'numeric', 'min:0'],
            'security_deposit' => ['nullable', 'numeric', 'min:0'],
            'advance_payment'  => ['nullable', 'numeric', 'min:0'],
            'due_day'          => ['required', 'integer', 'min:1', 'max:31'],
            'late_fee'         => ['nullable', 'numeric', 'min:0'],
            'terms'            => ['nullable', 'string', 'max:2000'],
            'status'           => ['required', 'in:active,pending,expired,terminated'],
        ]);

        $contract->update($validated);

        $this->activityLog->log('update_contract', 'contracts', $contract->id, $contract->contract_number);

        return redirect()->route('owner.contracts.show', $contract)->with('success', 'Contract terms updated.');
    }

    public function destroy(RentalContract $contract): RedirectResponse
    {
        $this->authorizeOwner($contract);

        $number = $contract->contract_number;
        $contract->delete();

        $this->activityLog->log('delete_contract', 'contracts', null, $number);

        return redirect()->route('owner.contracts.index')->with('success', "Contract {$number} deleted.");
    }

    private function authorizeOwner(RentalContract $contract): void
    {
        if ($contract->property?->house_owner_id !== auth()->user()->houseOwner?->id) {
            abort(403, 'Unauthorized.');
        }
    }
}

