<?php

namespace App\Notifications;

use App\Models\RentPayment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PaymentRecorded extends Notification
{
    use Queueable;

    public function __construct(private RentPayment $payment) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $billing = $this->payment->billing;

        return [
            'kind'           => 'payment_recorded',
            'payment_id'     => $this->payment->id,
            'payment_number' => $this->payment->payment_number,
            'amount'         => (float) $this->payment->amount,
            'balance'        => $billing ? (float) $billing->balance : null,
            'status'         => $billing?->status,
            'message'        => "Payment of ₱" . number_format((float) $this->payment->amount, 2) . " was recorded ({$this->payment->payment_number})." .
                ($billing ? " Remaining balance: ₱" . number_format((float) $billing->balance, 2) . " ({$billing->status})." : ''),
            'action_url'     => route('tenant.billings.show-payment', $this->payment),
            'action_label'   => 'View Receipt',
        ];
    }
}
