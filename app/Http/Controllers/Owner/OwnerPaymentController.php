<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\RentBilling;
use App\Models\RentPayment;
use App\Services\ActivityLogService;
use App\Services\BillingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OwnerPaymentController extends Controller
{
    public function __construct(
        private BillingService $billingService,
        private ActivityLogService $activityLog
    ) {}

    public function index(Request $request): View
    {
        $owner = auth()->user()->houseOwner;
        $propertyIds = $owner->properties()->pluck('id');

        $query = RentPayment::whereHas('billing.contract', function ($q) use ($propertyIds) {
                $q->whereIn('property_id', $propertyIds);
            })
            ->with(['tenant', 'billing.contract.property', 'recordedBy'])
            ->latest();

        if ($request->filled('method')) {
            $query->where('payment_method', $request->method);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('payment_number', 'like', "%{$search}%")
                  ->orWhere('reference_number', 'like', "%{$search}%")
                  ->orWhereHas('tenant', function ($tq) use ($search) {
                      $tq->where('first_name', 'like', "%{$search}%")
                         ->orWhere('last_name', 'like', "%{$search}%");
                  });
            });
        }

        $payments = $query->paginate(15)->withQueryString();

        return view('owner.payments.index', compact('payments'));
    }

    public function create(Request $request): View
    {
        $owner = auth()->user()->houseOwner;
        $propertyIds = $owner->properties()->pluck('id');

        $unpaidBillings = RentBilling::whereHas('contract', function ($q) use ($propertyIds) {
                $q->whereIn('property_id', $propertyIds);
            })
            ->whereIn('status', ['unpaid', 'partial', 'overdue'])
            ->with(['tenant', 'contract.property'])
            ->orderBy('due_date', 'asc')
            ->get();

        $selectedBillingId = $request->query('billing_id');

        return view('owner.payments.create', compact('unpaidBillings', 'selectedBillingId'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'billing_id'       => ['required', 'exists:rent_billings,id'],
            'amount'           => ['required', 'numeric', 'min:0.01'],
            'payment_date'     => ['required', 'date'],
            'payment_method'   => ['required', 'in:cash,gcash,bank_transfer,online_payment,other'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'notes'            => ['nullable', 'string', 'max:500'],
        ]);

        $billing = RentBilling::with('contract.property')->findOrFail($validated['billing_id']);

        if ($billing->contract?->property?->house_owner_id !== auth()->user()->houseOwner?->id) {
            abort(403, 'Unauthorized billing.');
        }

        if ($validated['amount'] > $billing->balance) {
            return back()->withInput()->with('error', "Payment amount (₱{$validated['amount']}) cannot exceed outstanding balance of ₱{$billing->balance}.");
        }

        $payment = $this->billingService->recordPayment($billing, $validated, auth()->user());

        $this->activityLog->log('record_payment', 'payment', $payment->id, $payment->payment_number, [
            'amount'  => $payment->amount,
            'billing' => $billing->billing_number,
        ]);

        return redirect()->route('owner.payments.show', $payment)
            ->with('success', "Payment receipt {$payment->payment_number} recorded successfully.");
    }

    public function show(RentPayment $payment): View
    {
        $this->authorizeOwner($payment);
        $payment->load(['tenant', 'billing.contract.property', 'recordedBy']);

        return view('owner.payments.show', compact('payment'));
    }

    private function authorizeOwner(RentPayment $payment): void
    {
        if ($payment->billing?->contract?->property?->house_owner_id !== auth()->user()->houseOwner?->id) {
            abort(403, 'Unauthorized.');
        }
    }
}

