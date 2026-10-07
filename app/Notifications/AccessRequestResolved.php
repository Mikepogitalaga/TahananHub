<?php

namespace App\Notifications;

use App\Models\OwnerAccessRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AccessRequestResolved extends Notification
{
    use Queueable;

    public function __construct(
        private OwnerAccessRequest $accessRequest,
        private string $decision, // approved | denied
        private ?string $ownerNotes = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $owner = $this->accessRequest->houseOwner;
        $approved = $this->decision === 'approved';

        return [
            'kind'                => 'access_request_resolved',
            'access_request_id'   => $this->accessRequest->id,
            'decision'            => $this->decision,
            'owner_name'          => $owner?->business_name ?? 'House Owner',
            'permissions'         => $this->accessRequest->permissions,
            'owner_notes'         => $this->ownerNotes,
            'message'             => $approved
                ? "Your access request for {$owner?->business_name} was APPROVED. Temporary read-only access is now active."
                : "Your access request for {$owner?->business_name} was DENIED by the House Owner.",
            'action_url'          => route('admin.access-requests.show', $this->accessRequest),
            'action_label'        => 'View Request',
        ];
    }
}
