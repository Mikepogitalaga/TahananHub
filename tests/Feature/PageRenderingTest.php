<?php

use App\Models\Announcement;
use App\Models\Expense;
use App\Models\MaintenanceRequest;

/**
 * Rendering sweep: every sidebar page must render for its role.
 * These catch missing view variables, broken relationships, and blade errors.
 */
test('all admin platform pages render', function () {
    $admin = makeAdmin();
    [, $owner] = makeOwner('sweep');
    $property = makeProperty($owner);

    $pages = [
        '/admin/dashboard',
        '/admin/users',
        '/admin/users/create',
        '/admin/owners',
        "/admin/owners/{$owner->id}",
        "/admin/owners/{$owner->id}/edit",
        '/admin/access-requests',
        '/admin/access-requests/create',
        '/admin/activity-logs',
        '/admin/backups',
        '/admin/settings',
    ];

    foreach ($pages as $page) {
        $this->actingAs($admin)->get($page)->assertOk();
    }
});

test('all owner workspace pages render', function () {
    [$ownerUser, $owner] = makeOwner('sweep');
    $property = makeProperty($owner);
    $room = makeRoom($property);
    $bed = makeBed($room);
    $tenant = makeTenant($owner);
    assignBedToTenant($tenant, $property, $room, $bed);
    $contract = makeContract($tenant, $property, $room, $bed);

    $billing = app(\App\Services\BillingService::class)
        ->generateMonthlyBilling($contract, now()->startOfMonth());

    $payment = app(\App\Services\BillingService::class)->recordPayment($billing, [
        'amount'         => 1000,
        'payment_date'   => now()->toDateString(),
        'payment_method' => 'cash',
    ], $ownerUser);

    Expense::create([
        'expense_number' => 'EXP-SWEEP-' . uniqid(),
        'property_id'    => $property->id,
        'house_owner_id' => $owner->id,
        'category'       => 'water',
        'description'    => 'Sweep test expense',
        'amount'         => 500,
        'expense_date'   => now()->toDateString(),
    ]);

    $announcement = Announcement::create([
        'house_owner_id' => $owner->id,
        'property_id'    => $property->id,
        'title'          => 'Sweep Announcement',
        'content'        => 'Test content',
        'publish_at'     => now(),
        'status'         => 'active',
    ]);

    $pages = [
        '/owner/dashboard',
        '/owner/properties',
        '/owner/properties/create',
        "/owner/properties/{$property->id}",
        "/owner/properties/{$property->id}/edit",
        '/owner/rooms',
        '/owner/rooms/create',
        "/owner/rooms/{$room->id}",
        "/owner/rooms/{$room->id}/edit",
        '/owner/tenants',
        '/owner/tenants/create',
        "/owner/tenants/{$tenant->id}",
        "/owner/tenants/{$tenant->id}/edit",
        '/owner/contracts',
        '/owner/contracts/create',
        "/owner/contracts/{$contract->id}",
        "/owner/contracts/{$contract->id}/edit",
        '/owner/billings',
        '/owner/billings/create',
        "/owner/billings/{$billing->id}",
        '/owner/payments',
        "/owner/payments/{$payment->id}",
        '/owner/expenses',
        '/owner/expenses/create',
        '/owner/maintenance',
        '/owner/announcements',
        '/owner/announcements/create',
        "/owner/announcements/{$announcement->id}/edit",
        '/owner/reports',
        '/owner/privacy',
    ];

    foreach ($pages as $page) {
        $this->actingAs($ownerUser)->get($page)->assertOk();
    }
});

test('all tenant portal pages render', function () {
    [, $owner] = makeOwner('sweept');
    $property = makeProperty($owner);
    $room = makeRoom($property);
    $bed = makeBed($room);
    $tenant = makeTenant($owner);
    assignBedToTenant($tenant, $property, $room, $bed);
    $contract = makeContract($tenant, $property, $room, $bed);

    $billing = app(\App\Services\BillingService::class)
        ->generateMonthlyBilling($contract, now()->startOfMonth());

    $payment = app(\App\Services\BillingService::class)->recordPayment($billing, [
        'amount'         => 500,
        'payment_date'   => now()->toDateString(),
        'payment_method' => 'cash',
    ], $owner->user);

    MaintenanceRequest::create([
        'request_number' => 'MNT-SWEEP-' . uniqid(),
        'tenant_id'      => $tenant->id,
        'property_id'    => $property->id,
        'room_id'        => $room->id,
        'bed_id'         => $bed->id,
        'category'       => 'room',
        'title'          => 'Sweep request',
        'description'    => 'Test',
        'priority'       => 'low',
        'status'         => 'pending',
    ]);

    Announcement::create([
        'house_owner_id' => $owner->id,
        'property_id'    => $property->id,
        'title'          => 'Tenant Sweep Notice',
        'content'        => 'Test content',
        'publish_at'     => now(),
        'status'         => 'active',
    ]);

    $pages = [
        '/tenant/dashboard',
        '/tenant/profile',
        '/tenant/contract',
        "/tenant/contracts/{$contract->id}",
        '/tenant/billings',
        "/tenant/billings/{$billing->id}",
        "/tenant/payments/{$payment->id}",
        '/tenant/maintenance',
        '/tenant/maintenance/create',
        '/tenant/announcements',
    ];

    foreach ($pages as $page) {
        $this->actingAs($tenant->user)->get($page)->assertOk();
    }

    // Maintenance detail page (first request)
    $request = MaintenanceRequest::where('tenant_id', $tenant->id)->first();
    $this->actingAs($tenant->user)->get("/tenant/maintenance/{$request->id}")->assertOk();
});

test('login and register pages render for guests', function () {
    $this->get('/')->assertOk();
    $this->get('/login')->assertOk();
    $this->get('/register')->assertOk();
});
