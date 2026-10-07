<?php

namespace App\Notifications;

use App\Models\RentBilling;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class BillingOverdue extends Notification
{
    use Queueable;

    public function __construct(private RentBilling $billing) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'kind'           => 'billing_overdue',
            'billing_id'     => $this->billing->id,
            'billing_number' => $this->billing->billing_number,
            'balance'        => (float) $this->billing->balance,
            'due_date'       => $this->billing->due_date,
            'message'        => "OVERDUE RENT: Billing {$this->billing->billing_number} was due on " .
                \Carbon\Carbon::parse($this->billing->due_date)->format('M d, Y') .
                ". Outstanding balance: ₱" . number_format((float) $this->billing->balance, 2) . ".",
            'action_url'     => route('tenant.billings.show-billing', $this->billing),
            'action_label'   => 'View Billing',
        ];
    }
}
