<?php

namespace App\Notifications;

use App\Models\OwnerAccessRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AccessRequestReceived extends Notification
{
    use Queueable;

    public function __construct(private OwnerAccessRequest $accessRequest) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $admin = $this->accessRequest->requestedBy;
        $owner = $this->accessRequest->houseOwner;

        return [
            'kind'                 => 'access_request_received',
            'access_request_id'    => $this->accessRequest->id,
            'admin_name'           => $admin?->name ?? 'Platform Administrator',
            'owner_name'           => $owner?->business_name ?? 'your workspace',
            'permissions'          => $this->accessRequest->permissions,
            'reason'               => $this->accessRequest->reason,
            'duration_minutes'     => $this->accessRequest->requested_duration_minutes,
            'message'              => "SUPER ADMIN ACCESS REQUEST: The platform administrator requested temporary access to your selected data.",
            'action_url'           => route('owner.privacy.index'),
            'action_label'         => 'Review Request',
        ];
    }
}
