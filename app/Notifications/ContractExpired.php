<?php

namespace App\Notifications;

use App\Models\RentalContract;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ContractExpired extends Notification
{
    use Queueable;

    public function __construct(private RentalContract $contract) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $tenant = $this->contract->tenant;

        return [
            'kind'            => 'contract_expired',
            'contract_id'     => $this->contract->id,
            'contract_number' => $this->contract->contract_number,
            'tenant_name'     => $tenant?->full_name,
            'end_date'        => $this->contract->end_date,
            'message'         => "Contract {$this->contract->contract_number} ({$tenant?->full_name}) has expired and was marked as EXPIRED.",
            'action_url'      => route('owner.contracts.show', $this->contract),
            'action_label'    => 'View Contract',
        ];
    }
}
