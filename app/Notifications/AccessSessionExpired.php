<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AccessSessionExpired extends Notification
{
    use Queueable;

    public function __construct(
        private int $ownerId,
        private string $ownerName,
        private array $permissions,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'kind'         => 'access_session_expired',
            'owner_id'     => $this->ownerId,
            'owner_name'   => $this->ownerName,
            'permissions'  => $this->permissions,
            'message'      => "Your temporary access session for {$this->ownerName} has expired. Access has been terminated automatically.",
            'action_url'   => route('admin.access-requests.index'),
            'action_label' => 'Access History',
        ];
    }
}
