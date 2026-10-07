<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\RentBilling;
use App\Models\RentPayment;
use Illuminate\View\View;

class TenantBillingController extends Controller
{
    public function index(): View
    {
        $tenant = auth()->user()->tenantProfile;

        if (!$tenant) {
            return view('tenant.no-profile');
        }

        $billings = $tenant->rentBillings()
            ->with(['contract.property'])
            ->latest('due_date')
            ->paginate(15);

        $payments = $tenant->rentPayments()
            ->with(['billing.contract.property'])
            ->latest('payment_date')
            ->paginate(15);

        $totalBalance = $tenant->rentBillings()
            ->whereIn('status', ['unpaid', 'partial', 'overdue'])
            ->sum('balance');

        return view('tenant.billings.index', compact('tenant', 'billings', 'payments', 'totalBalance'));
    }

    public function showBilling(RentBilling $billing): View
    {
        $tenant = auth()->user()->tenantProfile;

        if (!$tenant || $billing->tenant_id !== $tenant->id) {
            abort(403, 'Unauthorized.');
        }

        $billing->load(['contract.property.houseOwner.user', 'contract.room', 'contract.bed', 'payments.recordedBy']);

        return view('tenant.billings.show-billing', compact('tenant', 'billing'));
    }

    public function showPayment(RentPayment $payment): View
    {
        $tenant = auth()->user()->tenantProfile;

        if (!$tenant || $payment->tenant_id !== $tenant->id) {
            abort(403, 'Unauthorized.');
        }

        $payment->load(['billing.contract.property.houseOwner.user', 'billing.contract.room', 'recordedBy']);

        return view('tenant.billings.show-payment', compact('tenant', 'payment'));
    }
}

