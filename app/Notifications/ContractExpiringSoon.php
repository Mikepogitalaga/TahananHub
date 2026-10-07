<?php

namespace App\Notifications;

use App\Models\RentalContract;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ContractExpiringSoon extends Notification
{
    use Queueable;

    public function __construct(
        private RentalContract $contract,
        private int $daysRemaining,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $tenant = $this->contract->tenant;

        return [
            'kind'            => 'contract_expiring_soon',
            'contract_id'     => $this->contract->id,
            'contract_number' => $this->contract->contract_number,
            'tenant_name'     => $tenant?->full_name,
            'end_date'        => $this->contract->end_date,
            'days_remaining'  => $this->daysRemaining,
            'message'         => "Contract {$this->contract->contract_number} ({$tenant?->full_name}) expires in {$this->daysRemaining} day(s) on " .
                \Carbon\Carbon::parse($this->contract->end_date)->format('M d, Y') . ".",
            'action_url'      => route('owner.contracts.show', $this->contract),
            'action_label'    => 'View Contract',
        ];
    }
}
