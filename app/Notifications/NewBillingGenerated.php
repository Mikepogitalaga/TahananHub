<?php

namespace App\Notifications;

use App\Models\RentBilling;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewBillingGenerated extends Notification
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
            'kind'           => 'new_billing',
            'billing_id'     => $this->billing->id,
            'billing_number' => $this->billing->billing_number,
            'total_amount'   => (float) $this->billing->total_amount,
            'balance'        => (float) $this->billing->balance,
            'due_date'       => $this->billing->due_date,
            'message'        => "A new rent billing ({$this->billing->billing_number}) for ₱" . number_format((float) $this->billing->total_amount, 2) . " is due on " . \Carbon\Carbon::parse($this->billing->due_date)->format('M d, Y') . ".",
            'action_url'     => route('tenant.billings.show-billing', $this->billing),
            'action_label'   => 'View Billing',
        ];
    }
}
