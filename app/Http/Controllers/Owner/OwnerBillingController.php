<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\RentalContract;
use App\Models\RentBilling;
use App\Services\ActivityLogService;
use App\Services\BillingService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OwnerBillingController extends Controller
{
    public function __construct(
        private BillingService $billingService,
        private ActivityLogService $activityLog
    ) {}

    public function index(Request $request): View
    {
        $owner = auth()->user()->houseOwner;
        $propertyIds = $owner->properties()->pluck('id');

        $query = RentBilling::whereHas('contract', function ($q) use ($propertyIds) {
                $q->whereIn('property_id', $propertyIds);
            })
            ->with(['tenant', 'contract.property'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('billing_number', 'like', "%{$search}%")
                  ->orWhereHas('tenant', function ($tq) use ($search) {
                      $tq->where('first_name', 'like', "%{$search}%")
                         ->orWhere('last_name', 'like', "%{$search}%");
                  });
            });
        }

        $billings = $query->paginate(15)->withQueryString();

        return view('owner.billings.index', compact('billings'));
    }

    public function create(): View
    {
        $owner = auth()->user()->houseOwner;
        $propertyIds = $owner->properties()->pluck('id');

        $activeContracts = RentalContract::whereIn('property_id', $propertyIds)
            ->where('status', 'active')
            ->with(['tenant', 'property'])
            ->get();

        return view('owner.billings.create', compact('activeContracts'));
    }

    /**
     * Batch generate monthly billings for all active contracts for a given month.
     */
    public function generateBatch(Request $request): RedirectResponse
    {
        $request->validate([
            'month' => ['required', 'date_format:Y-m'],
        ]);

        $owner = auth()->user()->houseOwner;
        $propertyIds = $owner->properties()->pluck('id');

        $periodMonth = Carbon::createFromFormat('Y-m', $request->month);

        $contracts = RentalContract::whereIn('property_id', $propertyIds)
            ->where('status', 'active')
            ->get();

        $generatedCount = 0;
        foreach ($contracts as $contract) {
            $billing = $this->billingService->generateMonthlyBilling($contract, $periodMonth);
            if ($billing) {
                $generatedCount++;
            }
        }

        $this->activityLog->log('generate_billings', 'billing', null, "Generated {$generatedCount} billings for {$request->month}");

        return redirect()->route('owner.billings.index')
            ->with('success', "Batch generation complete: {$generatedCount} new monthly billing statement(s) created. Duplicates were automatically skipped.");
    }

    /**
     * Single manual billing creation.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'contract_id'               => ['required', 'exists:rental_contracts,id'],
            'billing_period_start'      => ['required', 'date'],
            'billing_period_end'        => ['required', 'date', 'after_or_equal:billing_period_start'],
            'due_date'                  => ['required', 'date'],
            'rent_amount'               => ['required', 'numeric', 'min:0'],
            'previous_balance'          => ['nullable', 'numeric', 'min:0'],
            'late_fee'                  => ['nullable', 'numeric', 'min:0'],
            'discount'                  => ['nullable', 'numeric', 'min:0'],
            'other_charges'             => ['nullable', 'numeric', 'min:0'],
            'other_charges_description' => ['nullable', 'string', 'max:255'],
            'notes'                     => ['nullable', 'string', 'max:500'],
        ]);

        $contract = RentalContract::with('property')->findOrFail($validated['contract_id']);
        if ($contract->property?->house_owner_id !== auth()->user()->houseOwner?->id) {
            abort(403, 'Unauthorized.');
        }

        // Server-side calculation
        $rent       = (float) $validated['rent_amount'];
        $prevBal    = (float) ($validated['previous_balance'] ?? 0);
        $lateFee    = (float) ($validated['late_fee'] ?? 0);
        $discount   = (float) ($validated['discount'] ?? 0);
        $otherChg   = (float) ($validated['other_charges'] ?? 0);

        $totalAmount = max(0, ($rent + $prevBal + $lateFee + $otherChg) - $discount);

        $billing = RentBilling::create(array_merge($validated, [
            'billing_number'   => $this->billingService->generateBillingNumber(),
            'tenant_id'        => $contract->tenant_id,
            'previous_balance' => $prevBal,
            'late_fee'         => $lateFee,
            'discount'         => $discount,
            'other_charges'    => $otherChg,
            'total_amount'     => $totalAmount,
            'amount_paid'      => 0,
            'balance'          => $totalAmount,
            'status'           => 'unpaid',
        ]));

        $this->activityLog->log('create_billing', 'billing', $billing->id, $billing->billing_number);

        return redirect()->route('owner.billings.show', $billing)->with('success', "Invoice {$billing->billing_number} issued.");
    }

    public function show(RentBilling $billing): View
    {
        $this->authorizeOwner($billing);
        $billing->load(['tenant', 'contract.property', 'payments.recordedBy']);

        return view('owner.billings.show', compact('billing'));
    }

    public function destroy(RentBilling $billing): RedirectResponse
    {
        $this->authorizeOwner($billing);

        if ($billing->payments()->count() > 0) {
            return back()->with('error', 'Cannot delete an invoice that has recorded payment transactions.');
        }

        $num = $billing->billing_number;
        $billing->delete();

        return redirect()->route('owner.billings.index')->with('success', "Billing {$num} deleted.");
    }

    private function authorizeOwner(RentBilling $billing): void
    {
        if ($billing->contract?->property?->house_owner_id !== auth()->user()->houseOwner?->id) {
            abort(403, 'Unauthorized.');
        }
    }
}

