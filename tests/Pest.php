<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(Tests\TestCase::class)
    ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

use App\Models\Bed;
use App\Models\HouseOwner;
use App\Models\Property;
use App\Models\RentalContract;
use App\Models\Room;
use App\Models\Tenant;
use App\Models\TenantAssignment;
use App\Models\User;

function makeAdmin(): User
{
    return User::create([
        'name'     => 'Test Super Admin',
        'email'    => 'admin@testing.test',
        'password' => 'password',
        'role'     => User::ROLE_SUPER_ADMIN,
        'status'   => 'active',
    ]);
}

/**
 * @return array{0: User, 1: HouseOwner}
 */
function makeOwner(string $slug = 'one'): array
{
    $user = User::create([
        'name'     => "Test Owner {$slug}",
        'email'    => "owner-{$slug}@testing.test",
        'password' => 'password',
        'role'     => User::ROLE_OWNER,
        'status'   => 'active',
    ]);

    $owner = HouseOwner::create([
        'user_id'          => $user->id,
        'business_name'    => "Boarding House {$slug}",
        'business_address' => "Fake Address {$slug}",
    ]);

    return [$user, $owner];
}

function makeProperty(HouseOwner $owner, string $code = 'TEST-PROP'): Property
{
    return Property::create([
        'house_owner_id' => $owner->id,
        'name'           => 'Test Property',
        'code'           => $code . '-' . uniqid(),
        'address'        => '123 Test Street',
        'municipality'   => 'Test City',
        'status'         => 'active',
    ]);
}

function makeRoom(Property $property, array $overrides = []): Room
{
    return Room::create(array_merge([
        'property_id' => $property->id,
        'room_number' => '101-' . uniqid(),
        'room_type'   => 'single',
        'capacity'    => 1,
        'rental_rate' => 3500,
        'status'      => 'available',
    ], $overrides));
}

function makeBed(Room $room, array $overrides = []): Bed
{
    return Bed::create(array_merge([
        'room_id'     => $room->id,
        'bed_number'  => 'A-' . uniqid(),
        'rental_rate' => 3500,
        'status'      => 'available',
    ], $overrides));
}

function makeTenant(HouseOwner $owner, array $overrides = []): Tenant
{
    $user = User::create([
        'name'     => 'Test Tenant',
        'email'    => 'tenant-' . uniqid() . '@testing.test',
        'password' => 'password',
        'role'     => User::ROLE_TENANT,
        'status'   => 'active',
    ]);

    return Tenant::create(array_merge([
        'house_owner_id' => $owner->id,
        'user_id'        => $user->id,
        'first_name'     => 'Testy',
        'last_name'      => 'Tenant-' . uniqid(),
        'contact_number' => '0917-000-0000',
        'status'         => 'active',
    ], $overrides));
}

/**
 * Create a current assignment and mark the bed occupied (mirrors controller logic).
 */
function assignBedToTenant(Tenant $tenant, Property $property, Room $room, Bed $bed): TenantAssignment
{
    $assignment = TenantAssignment::create([
        'tenant_id'   => $tenant->id,
        'property_id' => $property->id,
        'room_id'     => $room->id,
        'bed_id'      => $bed->id,
        'assigned_at' => now(),
        'is_current'  => true,
    ]);

    $bed->update(['status' => 'occupied']);

    return $assignment;
}

function makeContract(Tenant $tenant, Property $property, Room $room, Bed $bed, array $overrides = []): RentalContract
{
    return RentalContract::create(array_merge([
        'contract_number'  => 'CTR-' . strtoupper(uniqid()),
        'tenant_id'        => $tenant->id,
        'property_id'      => $property->id,
        'room_id'          => $room->id,
        'bed_id'           => $bed->id,
        'start_date'       => now()->subMonths(2)->toDateString(),
        'end_date'         => now()->addMonths(10)->toDateString(),
        'monthly_rent'     => $bed->rental_rate ?? 3500,
        'security_deposit' => 0,
        'advance_payment'  => 0,
        'due_day'          => 5,
        'late_fee'         => 200,
        'status'           => 'active',
    ], $overrides));
}
