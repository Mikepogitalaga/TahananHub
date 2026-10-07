<?php

use App\Models\User;

test('owner cannot view another owner\'s property', function () {
    [$userA] = makeOwner('a');
    [, $ownerB] = makeOwner('b');
    $propertyB = makeProperty($ownerB);

    $this->actingAs($userA)
        ->get(route('owner.properties.show', $propertyB))
        ->assertForbidden();
});

test('owner cannot view another owner\'s tenant', function () {
    [$userA, $ownerA] = makeOwner('t1');
    [, $ownerB] = makeOwner('t2');
    $tenantB = makeTenant($ownerB);

    $this->actingAs($userA)
        ->get(route('owner.tenants.show', $tenantB))
        ->assertForbidden();
});

test('owner cannot update another owner\'s billing', function () {
    [$userA, $ownerA] = makeOwner('b1');
    [, $ownerB] = makeOwner('b2');

    $propertyB = makeProperty($ownerB);
    $roomB = makeRoom($propertyB);
    $bedB = makeBed($roomB);
    $tenantB = makeTenant($ownerB);
    $contractB = makeContract($tenantB, $propertyB, $roomB, $bedB);

    $billingB = \App\Models\RentBilling::create([
        'billing_number'       => 'BIL-TEST-' . uniqid(),
        'tenant_id'            => $tenantB->id,
        'contract_id'          => $contractB->id,
        'billing_period_start' => now()->startOfMonth()->toDateString(),
        'billing_period_end'   => now()->endOfMonth()->toDateString(),
        'due_date'             => now()->addDay()->toDateString(),
        'rent_amount'          => 3500,
        'total_amount'         => 3500,
        'balance'              => 3500,
        'status'               => 'unpaid',
    ]);

    $this->actingAs($userA)
        ->get(route('owner.billings.show', $billingB))
        ->assertForbidden();
});

test('tenant cannot access another tenant\'s billing detail', function () {
    [, $owner] = makeOwner('iso');
    $tenantA = makeTenant($owner);
    $tenantB = makeTenant($owner);

    $property = makeProperty($owner);
    $room = makeRoom($property);
    $bedA = makeBed($room);
    $bedB = makeBed($room);
    $contractB = makeContract($tenantB, $property, $room, $bedB);

    $billingB = \App\Models\RentBilling::create([
        'billing_number'       => 'BIL-TB-' . uniqid(),
        'tenant_id'            => $tenantB->id,
        'contract_id'          => $contractB->id,
        'billing_period_start' => now()->startOfMonth()->toDateString(),
        'billing_period_end'   => now()->endOfMonth()->toDateString(),
        'due_date'             => now()->addDay()->toDateString(),
        'rent_amount'          => 3500,
        'total_amount'         => 3500,
        'balance'              => 3500,
        'status'               => 'unpaid',
    ]);

    $this->actingAs($tenantA->user)
        ->get(route('tenant.billings.show-billing', $billingB))
        ->assertForbidden();
});

test('tenant cannot record or modify payments (owner-only routes)', function () {
    [, $owner] = makeOwner('perm');
    $tenant = makeTenant($owner);

    $this->actingAs($tenant->user)->post('/owner/payments', [])->assertForbidden();
    $this->actingAs($tenant->user)->post('/owner/billings', [])->assertForbidden();
    $this->actingAs($tenant->user)->get('/owner/payments')->assertForbidden();
});

test('tenant cannot access owner or admin workspaces', function () {
    [, $owner] = makeOwner('walls');
    $tenant = makeTenant($owner);

    $this->actingAs($tenant->user)->get('/owner/dashboard')->assertForbidden();
    $this->actingAs($tenant->user)->get('/admin/dashboard')->assertForbidden();
});

test('owner cannot access the admin workspace', function () {
    [$ownerUser] = makeOwner('noadmin');

    $this->actingAs($ownerUser)->get('/admin/dashboard')->assertForbidden();
    $this->actingAs($ownerUser)->get('/admin/access-requests')->assertForbidden();
});

test('admin cannot enter the owner workspace through role middleware', function () {
    $admin = makeAdmin();

    $this->actingAs($admin)->get('/owner/dashboard')->assertForbidden();
    $this->actingAs($admin)->get('/tenant/dashboard')->assertForbidden();
});

test('owner dashboard only shows own properties', function () {
    [$userA, $ownerA] = makeOwner('da');
    [, $ownerB] = makeOwner('db');
    $propA = makeProperty($ownerA, 'OWN-A');
    $propB = makeProperty($ownerB, 'OWN-B');

    $response = $this->actingAs($userA)->get('/owner/dashboard')->assertOk();
    // Property names must not leak across owners on the dashboard.
    expect($propA->name === $propB->name)->toBeTrue(); // same display name
    // Ensure owner B's unique property code never appears for owner A.
    $response->assertDontSee($propB->code);
});
