<?php

namespace App\Services;

use App\Models\RentalContract;
use App\Models\RentBilling;
use App\Models\RentPayment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class BillingService
{
    /**
     * Generate monthly billing for an active contract.
     * Prevents duplicates for the same month/year.
     */
    public function generateMonthlyBilling(RentalContract $contract, Carbon $periodMonth): ?RentBilling
    {
        if ($contract->status !== 'active') {
            return null;
        }

        $startDate = $periodMonth->copy()->startOfMonth()->toDateString();
        $endDate   = $periodMonth->copy()->endOfMonth()->toDateString();

        // Check for duplicate billing for this contract and period
        // (whereDate is required: date casts store "Y-m-d 00:00:00" in the DB)
        $exists = RentBilling::where('contract_id', $contract->id)
            ->whereDate('billing_period_start', $startDate)
            ->exists();

        if ($exists) {
            return null;
        }

        // Determine due date for this month
        $dueDay = min($contract->due_day ?: 5, $periodMonth->daysInMonth);
        $dueDate = $periodMonth->copy()->day($dueDay)->toDateString();

        // Get previous unpaid balance if any
        $previousBalance = RentBilling::where('tenant_id', $contract->tenant_id)
            ->where('contract_id', $contract->id)
            ->where('status', '!=', 'paid')
            ->whereDate('billing_period_start', '<', $startDate)
            ->sum('balance');

        $rentAmount = (float) $contract->monthly_rent;
        $totalAmount = $rentAmount + (float) $previousBalance;

        return DB::transaction(function () use (
            $contract,
            $startDate,
            $endDate,
            $dueDate,
            $rentAmount,
            $previousBalance,
            $totalAmount
        ) {
            $billing = RentBilling::create([
                'billing_number'       => $this->generateBillingNumber(),
                'tenant_id'            => $contract->tenant_id,
                'contract_id'          => $contract->id,
                'billing_period_start' => $startDate,
                'billing_period_end'   => $endDate,
                'due_date'             => $dueDate,
                'rent_amount'          => $rentAmount,
                'previous_balance'     => $previousBalance,
                'late_fee'             => 0,
                'discount'             => 0,
                'other_charges'        => 0,
                'total_amount'         => $totalAmount,
                'amount_paid'          => 0,
                'balance'              => $totalAmount,
                'status'               => 'unpaid',
            ]);

            // Notify the tenant (if a portal account is linked).
            $billing->tenant?->user?->notify(new \App\Notifications\NewBillingGenerated($billing));

            return $billing;
        });
    }

    /**
     * Record a payment against a billing record.
     */
    public function recordPayment(RentBilling $billing, array $data, User $recordedBy): RentPayment
    {
        $amount = (float) $data['amount'];
        if ($amount <= 0) {
            throw new RuntimeException('Payment amount must be greater than zero.');
        }

        return DB::transaction(function () use ($billing, $amount, $data, $recordedBy) {
            $payment = RentPayment::create([
                'payment_number'      => $this->generatePaymentNumber(),
                'tenant_id'           => $billing->tenant_id,
                'billing_id'          => $billing->id,
                'amount'              => $amount,
                'payment_date'        => $data['payment_date'] ?? Carbon::now()->toDateString(),
                'payment_method'      => $data['payment_method'] ?? 'cash',
                'reference_number'    => $data['reference_number'] ?? null,
                'notes'               => $data['notes'] ?? null,
                'recorded_by_user_id' => $recordedBy->id,
            ]);

            // Update billing amounts and status
            $newPaid = (float) $billing->amount_paid + $amount;
            $newBalance = max(0, (float) $billing->total_amount - $newPaid);

            $status = 'partial';
            if ($newBalance <= 0) {
                $status = 'paid';
            }

            $billing->update([
                'amount_paid' => $newPaid,
                'balance'     => $newBalance,
                'status'      => $status,
            ]);

            // Notify the tenant that their payment was recorded.
            $payment->tenant?->user?->notify(new \App\Notifications\PaymentRecorded($payment));

            return $payment;
        });
    }

    /**
     * Mark unpaid billings past due date as overdue.
     */
    public function markOverdueBillings(): int
    {
        $today = Carbon::now()->toDateString();

        return RentBilling::whereIn('status', ['unpaid', 'partial'])
            ->whereDate('due_date', '<', $today)
            ->update(['status' => 'overdue']);
    }

    /**
     * Generate unique billing invoice number (e.g. BIL-202610-0001).
     */
    public function generateBillingNumber(): string
    {
        $prefix = 'BIL-' . date('Ym') . '-';
        $count = RentBilling::where('billing_number', 'like', "{$prefix}%")->count() + 1;
        return $prefix . str_pad((string) $count, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Generate unique payment receipt number (e.g. PAY-202610-0001).
     */
    public function generatePaymentNumber(): string
    {
        $prefix = 'PAY-' . date('Ym') . '-';
        $count = RentPayment::where('payment_number', 'like', "{$prefix}%")->count() + 1;
        return $prefix . str_pad((string) $count, 4, '0', STR_PAD_LEFT);
    }
}

