<?php

use App\Models\OwnerAccessRequest;
use App\Models\OwnerAccessSession;
use App\Models\PrivacyAuditLog;
use App\Notifications\AccessRequestReceived;
use App\Notifications\AccessRequestResolved;
use App\Notifications\AccessSessionExpired;
use App\Services\PrivacyAccessService;
use Illuminate\Support\Facades\Notification;

/**
 * Shared fixture: admin + owner (with property/room/bed/tenant).
 *
 * @return array{0: \App\Models\User, 1: \App\Models\User, 2: \App\Models\HouseOwner, 3: \App\Models\Property, 4: \App\Models\Room, 5: \App\Models\Bed, 6: \App\Models\Tenant}
 */
function privacyFixture(): array
{
    $admin = makeAdmin();
    [$ownerUser, $owner] = makeOwner('privacy');
    $property = makeProperty($owner);
    $room = makeRoom($property);
    $bed = makeBed($room);
    $tenant = makeTenant($owner);

    return [$admin, $ownerUser, $owner, $property, $room, $bed, $tenant];
}

test('admin is denied protected owner data by default', function () {
    [$admin, , $owner] = privacyFixture();

    $this->actingAs($admin)
        ->get(route('admin.protected.tenants', $owner))
        ->assertForbidden();

    $this->actingAs($admin)
        ->get(route('admin.protected.payments', $owner))
        ->assertForbidden();
});

test('owner receives a notification when admin requests access', function () {
    Notification::fake();
    [$admin, , $owner] = privacyFixture();

    app(PrivacyAccessService::class)->requestAccess(
        $admin,
        $owner,
        ['VIEW_TENANTS'],
        'Investigating a reported issue (test).',
        60
    );

    Notification::assertSentTo($owner->user, AccessRequestReceived::class);
    expect(OwnerAccessRequest::where('house_owner_id', $owner->id)->where('status', 'pending')->exists())->toBeTrue();
    expect(PrivacyAuditLog::where('action', 'access_requested')->where('house_owner_id', $owner->id)->exists())->toBeTrue();
});

test('approved access grants temporary read-only access with audit logging', function () {
    Notification::fake();
    [$admin, $ownerUser, $owner] = privacyFixture();

    $service = app(PrivacyAccessService::class);
    $requestModel = $service->requestAccess($admin, $owner, ['VIEW_TENANTS'], 'Audit (test).', 60);

    $this->actingAs($ownerUser)
        ->post(route('owner.privacy.respond', $requestModel), ['action' => 'approve'])
        ->assertRedirect(route('owner.privacy.index'));

    Notification::assertSentTo($admin, AccessRequestResolved::class);

    $this->actingAs($admin)->get(route('admin.protected.tenants', $owner))->assertOk();

    expect(PrivacyAuditLog::where('action', 'view_protected_data')
        ->where('house_owner_id', $owner->id)
        ->where('admin_user_id', $admin->id)->exists())->toBeTrue();

    expect(OwnerAccessSession::where('house_owner_id', $owner->id)->where('is_active', true)->exists())->toBeTrue();
});

test('denied request keeps admin blocked', function () {
    Notification::fake();
    [$admin, $ownerUser, $owner] = privacyFixture();

    $service = app(PrivacyAccessService::class);
    $requestModel = $service->requestAccess($admin, $owner, ['VIEW_TENANTS'], 'Audit (test).', 60);

    $this->actingAs($ownerUser)
        ->post(route('owner.privacy.respond', $requestModel), ['action' => 'deny', 'notes' => 'No'])
        ->assertRedirect(route('owner.privacy.index'));

    $this->actingAs($admin)->get(route('admin.protected.tenants', $owner))->assertForbidden();
    expect(OwnerAccessSession::count())->toBe(0);
    expect($requestModel->fresh()->status)->toBe('denied');
    expect(PrivacyAuditLog::where('action', 'access_denied')->exists())->toBeTrue();
});

test('access expires automatically after the approved duration', function () {
    Notification::fake();
    [$admin, $ownerUser, $owner] = privacyFixture();

    $service = app(PrivacyAccessService::class);
    $requestModel = $service->requestAccess($admin, $owner, ['VIEW_TENANTS'], 'Audit (test).', 15);

    $this->actingAs($ownerUser)
        ->post(route('owner.privacy.respond', $requestModel), ['action' => 'approve']);

    // Still valid
    $this->actingAs($admin)->get(route('admin.protected.tenants', $owner))->assertOk();

    // Travel past the approved 15 minutes
    $this->travel(16)->minutes();

    $this->actingAs($admin)->get(route('admin.protected.tenants', $owner))->assertForbidden();

    // Scheduler-style expiry marks session + request expired and notifies the admin
    $service->expireOldSessions();

    expect(OwnerAccessSession::where('house_owner_id', $owner->id)->where('is_active', true)->exists())->toBeFalse();
    expect($requestModel->fresh()->status)->toBe('expired');
    expect(PrivacyAuditLog::where('action', 'access_expired')->exists())->toBeTrue();
    Notification::assertSentTo($admin, AccessSessionExpired::class);
});

test('owner can revoke access immediately', function () {
    Notification::fake();
    [$admin, $ownerUser, $owner] = privacyFixture();

    $service = app(PrivacyAccessService::class);
    $requestModel = $service->requestAccess($admin, $owner, ['VIEW_TENANTS'], 'Audit (test).', 60);

    $this->actingAs($ownerUser)
        ->post(route('owner.privacy.respond', $requestModel), ['action' => 'approve']);

    $session = OwnerAccessSession::where('house_owner_id', $owner->id)->firstOrFail();

    $this->actingAs($admin)->get(route('admin.protected.tenants', $owner))->assertOk();

    $this->actingAs($ownerUser)
        ->post(route('owner.privacy.revoke', $session))
        ->assertRedirect(route('owner.privacy.index'));

    $this->actingAs($admin)->get(route('admin.protected.tenants', $owner))->assertForbidden();
    expect($session->fresh()->revoked_at)->not->toBeNull();
    expect(PrivacyAuditLog::where('action', 'access_revoked')->exists())->toBeTrue();
});

test('granular permissions restrict which sections are visible', function () {
    Notification::fake();
    [$admin, $ownerUser, $owner] = privacyFixture();

    $service = app(PrivacyAccessService::class);
    $requestModel = $service->requestAccess($admin, $owner, ['VIEW_TENANTS'], 'Only tenants (test).', 60);

    $this->actingAs($ownerUser)
        ->post(route('owner.privacy.respond', $requestModel), ['action' => 'approve']);

    // Granted
    $this->actingAs($admin)->get(route('admin.protected.tenants', $owner))->assertOk();
    // Not granted
    $this->actingAs($admin)->get(route('admin.protected.payments', $owner))->assertForbidden();
    $this->actingAs($admin)->get(route('admin.protected.expenses', $owner))->assertForbidden();
    $this->actingAs($admin)->get(route('admin.protected.contracts', $owner))->assertForbidden();
});

test('privacy approval for one owner does not unlock another owner', function () {
    Notification::fake();
    [$admin, , $ownerA] = privacyFixture();
    [, $ownerB] = privacyFixture2();

    $service = app(PrivacyAccessService::class);
    $requestModel = $service->requestAccess($admin, $ownerA, ['VIEW_TENANTS'], 'Audit A (test).', 60);
    $service->approveAccess($requestModel, $ownerA->user);

    $this->actingAs($admin)->get(route('admin.protected.tenants', $ownerA))->assertOk();
    $this->actingAs($admin)->get(route('admin.protected.tenants', $ownerB))->assertForbidden();
});

test('access request and history pages render for both roles', function () {
    Notification::fake();
    [$admin, $ownerUser, $owner] = privacyFixture();

    $service = app(PrivacyAccessService::class);
    $service->requestAccess($admin, $owner, ['VIEW_PAYMENTS'], 'Rendering check (test).', 30);

    $this->actingAs($admin)->get(route('admin.access-requests.index'))->assertOk();
    $this->actingAs($admin)->get(route('admin.access-requests.create'))->assertOk();
    $this->actingAs($ownerUser)->get(route('owner.privacy.index'))->assertOk();
    $this->actingAs($admin)->get(route('admin.owners.show', $owner))->assertOk();
});

/**
 * Second-owner fixture used by the cross-owner scoping test.
 *
 * @return array{0: \App\Models\User, 1: \App\Models\HouseOwner}
 */
function privacyFixture2(): array
{
    [$user, $owner] = makeOwner('privacy2');
    makeProperty($owner);

    return [$user, $owner];
}

