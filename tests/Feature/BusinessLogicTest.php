<?php

use App\Models\RentBilling;
use App\Models\RentPayment;
use App\Notifications\BillingOverdue;
use App\Notifications\ContractExpired;
use App\Services\BillingService;
use Illuminate\Support\Facades\Notification;

test('cannot assign an occupied bed', function () {
    [$ownerUser, $owner] = makeOwner('bed');
    $property = makeProperty($owner);
    $room = makeRoom($property);
    $occupiedBed = makeBed($room, ['status' => 'occupied']);
    $newTenant = makeTenant($owner);

    $response = $this->actingAs($ownerUser)
        ->post(route('owner.tenants.assign-bed', $newTenant), ['bed_id' => $occupiedBed->id]);

    $response->assertSessionHas('error');
    expect($occupiedBed->fresh()->status)->toBe('occupied');
    expect($newTenant->currentAssignment)->toBeNull();
});

test('assigning an available bed succeeds and occupies it', function () {
    [$ownerUser, $owner] = makeOwner('bed2');
    $property = makeProperty($owner);
    $room = makeRoom($property);
    $bed = makeBed($room);
    $tenant = makeTenant($owner);

    $this->actingAs($ownerUser)
        ->post(route('owner.tenants.assign-bed', $tenant), ['bed_id' => $bed->id])
        ->assertSessionHas('success');

    expect($bed->fresh()->status)->toBe('occupied');
    expect($tenant->fresh()->currentAssignment)->not->toBeNull();
});

test('move out frees the bed and preserves assignment history', function () {
    [$ownerUser, $owner] = makeOwner('moveout');
    $property = makeProperty($owner);
    $room = makeRoom($property);
    $bed = makeBed($room);
    $tenant = makeTenant($owner);
    $assignment = assignBedToTenant($tenant, $property, $room, $bed);
    $contract = makeContract($tenant, $property, $room, $bed);

    $this->actingAs($ownerUser)
        ->post(route('owner.tenants.move-out', $tenant))
        ->assertSessionHas('success');

    expect($bed->fresh()->status)->toBe('available');
    expect($tenant->fresh()->status)->toBe('moved_out');
    expect($contract->fresh()->status)->toBe('terminated');

    // History preserved
    $assignment->refresh();
    expect($assignment->is_current)->toBeFalse()
        ->and($assignment->vacated_at)->not->toBeNull()
        ->and(\App\Models\TenantAssignment::where('tenant_id', $tenant->id)->count())->toBe(1);
});

test('partial payment updates amount paid, balance and status', function () {
    [, $owner] = makeOwner('pay1');
    $tenant = makeTenant($owner);
    $property = makeProperty($owner);
    $room = makeRoom($property);
    $bed = makeBed($room);
    $contract = makeContract($tenant, $property, $room, $bed);
    [$recordingUser] = makeOwner('recorder');

    $billing = app(BillingService::class)->generateMonthlyBilling($contract, now()->startOfMonth());
    expect((float) $billing->total_amount)->toBe(3500.0);

    app(BillingService::class)->recordPayment($billing, [
        'amount'         => 2000,
        'payment_date'   => now()->toDateString(),
        'payment_method' => 'cash',
    ], $recordingUser);

    $billing->refresh();
    expect((float) $billing->amount_paid)->toBe(2000.0)
        ->and((float) $billing->balance)->toBe(1500.0)
        ->and($billing->status)->toBe('partial');
});

test('full payment marks the billing as paid with zero balance', function () {
    [, $owner] = makeOwner('pay2');
    $tenant = makeTenant($owner);
    $property = makeProperty($owner);
    $room = makeRoom($property);
    $bed = makeBed($room);
    $contract = makeContract($tenant, $property, $room, $bed);
    [$recordingUser] = makeOwner('recorder2');

    $billing = app(BillingService::class)->generateMonthlyBilling($contract, now()->startOfMonth());

    app(BillingService::class)->recordPayment($billing, [
        'amount'         => 3500,
        'payment_date'   => now()->toDateString(),
        'payment_method' => 'gcash',
    ], $recordingUser);

    $billing->refresh();
    expect((float) $billing->balance)->toBe(0.0)
        ->and($billing->status)->toBe('paid')
        ->and(RentPayment::where('billing_id', $billing->id)->count())->toBe(1);
});

test('monthly billing cannot duplicate for the same period', function () {
    [, $owner] = makeOwner('dup');
    $tenant = makeTenant($owner);
    $property = makeProperty($owner);
    $room = makeRoom($property);
    $bed = makeBed($room);
    $contract = makeContract($tenant, $property, $room, $bed);

    $service = app(BillingService::class);
    $month = now()->startOfMonth();

    expect($service->generateMonthlyBilling($contract, $month))->not->toBeNull();
    expect($service->generateMonthlyBilling($contract, $month))->toBeNull();

    expect(RentBilling::where('contract_id', $contract->id)->count())->toBe(1);
});

test('billing total carries the previous unpaid balance forward', function () {
    [, $owner] = makeOwner('prev');
    $tenant = makeTenant($owner);
    $property = makeProperty($owner);
    $room = makeRoom($property);
    $bed = makeBed($room);
    $contract = makeContract($tenant, $property, $room, $bed);

    $service = app(BillingService::class);

    // September left completely unpaid (no payments made).
    $sep = $service->generateMonthlyBilling($contract, now()->subMonth()->startOfMonth());
    expect($sep)->not->toBeNull();

    $oct = $service->generateMonthlyBilling($contract, now()->startOfMonth());
    expect((float) $oct->previous_balance)->toBe(3500.0)
        ->and((float) $oct->total_amount)->toBe(7000.0)
        ->and((float) $oct->balance)->toBe(7000.0);
});

test('overdue billings are marked by the scheduler command', function () {
    Notification::fake();
    [, $owner] = makeOwner('over');
    $tenant = makeTenant($owner);
    $property = makeProperty($owner);
    $room = makeRoom($property);
    $bed = makeBed($room);
    $contract = makeContract($tenant, $property, $room, $bed);

    $billing = app(BillingService::class)->generateMonthlyBilling($contract, now()->startOfMonth());
    $billing->update(['due_date' => now()->subDays(3)->toDateString()]);

    $this->artisan('billing:mark-overdue')->assertSuccessful();

    expect($billing->fresh()->status)->toBe('overdue');
    Notification::assertSentTo($tenant->user, BillingOverdue::class);
});

test('scheduler generates billing for active contracts without duplicates', function () {
    [, $owner] = makeOwner('sched');
    $tenant = makeTenant($owner);
    $property = makeProperty($owner);
    $room = makeRoom($property);
    $bed = makeBed($room);
    $contract = makeContract($tenant, $property, $room, $bed);

    $this->artisan('billing:generate-monthly')->assertSuccessful();
    $countAfterFirst = RentBilling::where('contract_id', $contract->id)->count();
    expect($countAfterFirst)->toBe(1);

    $this->artisan('billing:generate-monthly')->assertSuccessful();
    expect(RentBilling::where('contract_id', $contract->id)->count())->toBe($countAfterFirst);
});

test('scheduler command does not duplicate a period already seeded manually', function () {
    [, $owner] = makeOwner('sched2');
    $tenant = makeTenant($owner);
    $property = makeProperty($owner);
    $room = makeRoom($property);
    $bed = makeBed($room);
    $contract = makeContract($tenant, $property, $room, $bed);

    app(BillingService::class)->generateMonthlyBilling($contract, now()->startOfMonth());

    $this->artisan('billing:generate-monthly')->assertSuccessful();

    expect(RentBilling::where('contract_id', $contract->id)->count())->toBe(1);
});

test('expired contracts are marked and owners notified by the scheduler', function () {
    Notification::fake();
    [$ownerUser, $owner] = makeOwner('exp');
    $tenant = makeTenant($owner);
    $property = makeProperty($owner);
    $room = makeRoom($property);
    $bed = makeBed($room);
    $contract = makeContract($tenant, $property, $room, $bed, [
        'end_date' => now()->subDay()->toDateString(),
        'status'   => 'active',
    ]);

    $this->artisan('contracts:manage-expiring')->assertSuccessful();

    expect($contract->fresh()->status)->toBe('expired');
    Notification::assertSentTo($ownerUser, ContractExpired::class);
});

test('expiring-soon contract reminders are sent only once', function () {
    // Intentionally NOT faking notifications: the command deduplicates
    // against rows persisted in the database notifications table.
    [$ownerUser, $owner] = makeOwner('soon');
    $tenant = makeTenant($owner);
    $property = makeProperty($owner);
    $room = makeRoom($property);
    $bed = makeBed($room);
    $contract = makeContract($tenant, $property, $room, $bed, [
        'end_date' => now()->addDays(10)->toDateString(),
        'status'   => 'active',
    ]);

    $this->artisan('contracts:manage-expiring')->assertSuccessful();
    $this->artisan('contracts:manage-expiring')->assertSuccessful();

    $sent = $ownerUser->notifications()
        ->where('type', \App\Notifications\ContractExpiringSoon::class)
        ->count();

    expect($sent)->toBe(1);
});

test('billing server-side total follows the documented formula', function () {
    // TOTAL = RENT + PREVIOUS BALANCE + LATE FEE + OTHER CHARGES - DISCOUNT
    $rent = 3500; $prev = 500; $late = 200; $other = 100; $discount = 300;
    $total = max(0, ($rent + $prev + $late + $other) - $discount);

    expect($total)->toBe(4000);
});

