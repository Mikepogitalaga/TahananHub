<?php

use App\Models\Bed;
use App\Models\HouseOwner;
use App\Models\Property;
use App\Models\RentalRequest;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ---------------------------------------------------------------------------
// Helpers (names chosen to avoid conflict with Pest.php globals)
// ---------------------------------------------------------------------------

function mktSetup(array $overrides = []): array
{
    static $n = 0; $n++;
    $ownerUser = User::create([
        'name' => "MktOwner{$n}", 'email' => "mktowner{$n}@t.test",
        'password' => 'password', 'role' => 'owner', 'status' => 'active',
    ]);
    $owner = HouseOwner::create(['user_id' => $ownerUser->id, 'subscription_plan' => 'free']);
    $property = Property::create(array_merge([
        'house_owner_id' => $owner->id, 'name' => "House{$n}", 'code' => "MKT-{$n}",
        'address' => '1 St', 'status' => 'active', 'is_listed' => true,
        'public_description' => 'desc', 'amenities' => ['WiFi'],
    ], $overrides));
    $room = Room::create([
        'property_id' => $property->id, 'room_number' => "R{$n}",
        'room_type' => 'single', 'capacity' => 2, 'rental_rate' => 3500, 'status' => 'available',
    ]);
    $bed = Bed::create([
        'room_id' => $room->id, 'bed_number' => 'A', 'rental_rate' => 3500, 'status' => 'available',
    ]);
    return compact('ownerUser', 'owner', 'property', 'room', 'bed');
}

function mktTenant(): User
{
    static $n = 0; $n++;
    return User::create([
        'name' => "TUser{$n}", 'email' => "tuser{$n}@t.test",
        'password' => 'password', 'role' => 'tenant', 'status' => 'active',
    ]);
}

function mktRequest(array $d, string $num): RentalRequest
{
    return RentalRequest::create([
        'request_number'       => $num,
        'user_id'              => $d['user_id'],
        'property_id'          => $d['property_id'],
        'room_id'              => $d['room_id'],
        'bed_id'               => $d['bed_id'],
        'house_owner_id'       => $d['house_owner_id'],
        'monthly_rent'         => 3500,
        'desired_move_in_date' => now()->addDays(7)->toDateString(),
        'status'               => 'pending',
    ]);
}

// ---------------------------------------------------------------------------
// Public browsing — no login required
// ---------------------------------------------------------------------------

test('guest can browse houses index', function () {
    ['property' => $p] = mktSetup();
    $this->get(route('houses.index'))->assertOk()->assertSee($p->name);
});

test('guest can view public property detail', function () {
    ['property' => $p] = mktSetup();
    $this->get(route('houses.show', $p))->assertOk()->assertSee($p->name);
});

test('guest cannot view unlisted property', function () {
    ['property' => $p] = mktSetup(['is_listed' => false]);
    $this->get(route('houses.show', $p))->assertNotFound();
});

test('guest cannot view inactive property', function () {
    ['property' => $p] = mktSetup(['status' => 'inactive']);
    $this->get(route('houses.show', $p))->assertNotFound();
});

test('unlisted property does not appear in houses index', function () {
    mktSetup(['is_listed' => false, 'name' => 'HiddenHouse999']);
    $this->get(route('houses.index'))->assertOk()->assertDontSee('HiddenHouse999');
});

test('guest can view public room page', function () {
    ['property' => $p, 'room' => $r] = mktSetup();
    $this->get(route('houses.room', [$p, $r]))->assertOk();
});

// ---------------------------------------------------------------------------
// Authentication redirect flow
// ---------------------------------------------------------------------------

test('guest is redirected to login when accessing rental request create', function () {
    ['property' => $p, 'room' => $r, 'bed' => $b] = mktSetup();
    $this->get(route('tenant.rental-requests.create', [
        'property' => $p->id, 'room' => $r->id, 'bed' => $b->id,
    ]))->assertRedirect(route('login'));
});

test('after tenant login _intended url is honoured', function () {
    ['property' => $p, 'room' => $r, 'bed' => $b] = mktSetup();
    $tenant   = mktTenant();
    $intended = route('tenant.rental-requests.create', [
        'property' => $p->id, 'room' => $r->id, 'bed' => $b->id,
    ]);
    $this->post(route('login.attempt'), [
        'email' => $tenant->email, 'password' => 'password', '_intended' => $intended,
    ])->assertRedirect($intended);
});

// ---------------------------------------------------------------------------
// Tenant registration
// ---------------------------------------------------------------------------

test('visitor can register as tenant', function () {
    $this->post(route('register.tenant.store'), [
        'name' => 'New Tenant', 'email' => 'newtenant@test.com',
        'password' => 'password123', 'password_confirmation' => 'password123',
    ])->assertRedirect(route('houses.index'));
    $this->assertDatabaseHas('users', ['email' => 'newtenant@test.com', 'role' => 'tenant']);
});

// ---------------------------------------------------------------------------
// Rental request — tenant
// ---------------------------------------------------------------------------

test('tenant can submit a rental request', function () {
    ['property' => $p, 'room' => $r, 'bed' => $b] = mktSetup();
    $tenant = mktTenant();
    $this->actingAs($tenant)->post(route('tenant.rental-requests.store'), [
        'property_id' => $p->id, 'room_id' => $r->id, 'bed_id' => $b->id,
        'desired_move_in_date' => now()->addDays(7)->toDateString(),
    ])->assertRedirect();
    $this->assertDatabaseHas('rental_requests', ['user_id' => $tenant->id, 'bed_id' => $b->id, 'status' => 'pending']);
});

test('tenant cannot submit request for unavailable bed', function () {
    ['property' => $p, 'room' => $r, 'bed' => $b] = mktSetup();
    $b->update(['status' => 'occupied']);
    $tenant = mktTenant();
    $this->actingAs($tenant)->post(route('tenant.rental-requests.store'), [
        'property_id' => $p->id, 'room_id' => $r->id, 'bed_id' => $b->id,
        'desired_move_in_date' => now()->addDays(7)->toDateString(),
    ])->assertSessionHas('error');
});

test('tenant can see own rental request', function () {
    ['property' => $p, 'room' => $r, 'bed' => $b, 'owner' => $o] = mktSetup();
    $tenant = mktTenant();
    $req = mktRequest(['user_id' => $tenant->id, 'property_id' => $p->id, 'room_id' => $r->id, 'bed_id' => $b->id, 'house_owner_id' => $o->id], 'RR-T-001');
    $this->actingAs($tenant)->get(route('tenant.rental-requests.show', $req))->assertOk();
});

test('tenant cannot see another tenants rental request', function () {
    ['property' => $p, 'room' => $r, 'bed' => $b, 'owner' => $o] = mktSetup();
    $t1 = mktTenant(); $t2 = mktTenant();
    $req = mktRequest(['user_id' => $t1->id, 'property_id' => $p->id, 'room_id' => $r->id, 'bed_id' => $b->id, 'house_owner_id' => $o->id], 'RR-T-002');
    $this->actingAs($t2)->get(route('tenant.rental-requests.show', $req))->assertForbidden();
});

test('tenant can withdraw pending request', function () {
    ['property' => $p, 'room' => $r, 'bed' => $b, 'owner' => $o] = mktSetup();
    $tenant = mktTenant();
    $req = mktRequest(['user_id' => $tenant->id, 'property_id' => $p->id, 'room_id' => $r->id, 'bed_id' => $b->id, 'house_owner_id' => $o->id], 'RR-T-003');
    $this->actingAs($tenant)->patch(route('tenant.rental-requests.cancel', $req))->assertRedirect();
    $this->assertDatabaseHas('rental_requests', ['id' => $req->id, 'status' => 'withdrawn']);
});

// ---------------------------------------------------------------------------
// Owner rental requests
// ---------------------------------------------------------------------------

test('owner can see own property requests', function () {
    ['ownerUser' => $ou, 'property' => $p, 'room' => $r, 'bed' => $b, 'owner' => $o] = mktSetup();
    $tenant = mktTenant();
    mktRequest(['user_id' => $tenant->id, 'property_id' => $p->id, 'room_id' => $r->id, 'bed_id' => $b->id, 'house_owner_id' => $o->id], 'RR-O-001');
    $this->actingAs($ou)->get(route('owner.rental-requests.index'))->assertOk()->assertSee($tenant->name);
});

test('owner cannot see another owners request', function () {
    ['ownerUser' => $ou] = mktSetup();
    $s2 = mktSetup(['code' => 'OTHER-X']);
    $tenant = mktTenant();
    $req = mktRequest(['user_id' => $tenant->id, 'property_id' => $s2['property']->id, 'room_id' => $s2['room']->id, 'bed_id' => $s2['bed']->id, 'house_owner_id' => $s2['owner']->id], 'RR-O-002');
    $this->actingAs($ou)->get(route('owner.rental-requests.show', $req))->assertForbidden();
});

test('owner can approve request and bed becomes reserved', function () {
    ['ownerUser' => $ou, 'property' => $p, 'room' => $r, 'bed' => $b, 'owner' => $o] = mktSetup();
    $tenant = mktTenant();
    $req = mktRequest(['user_id' => $tenant->id, 'property_id' => $p->id, 'room_id' => $r->id, 'bed_id' => $b->id, 'house_owner_id' => $o->id], 'RR-O-003');
    $this->actingAs($ou)->post(route('owner.rental-requests.approve', $req))->assertRedirect();
    $this->assertDatabaseHas('rental_requests', ['id' => $req->id, 'status' => 'approved']);
    $this->assertDatabaseHas('beds', ['id' => $b->id, 'status' => 'reserved']);
});

test('owner can reject request', function () {
    ['ownerUser' => $ou, 'property' => $p, 'room' => $r, 'bed' => $b, 'owner' => $o] = mktSetup();
    $tenant = mktTenant();
    $req = mktRequest(['user_id' => $tenant->id, 'property_id' => $p->id, 'room_id' => $r->id, 'bed_id' => $b->id, 'house_owner_id' => $o->id], 'RR-O-004');
    $this->actingAs($ou)->post(route('owner.rental-requests.reject', $req))->assertRedirect();
    $this->assertDatabaseHas('rental_requests', ['id' => $req->id, 'status' => 'rejected']);
    $this->assertDatabaseHas('beds', ['id' => $b->id, 'status' => 'available']);
});

// ---------------------------------------------------------------------------
// Bed reservation — double booking prevention
// ---------------------------------------------------------------------------

test('reserved bed cannot be approved again', function () {
    ['ownerUser' => $ou, 'property' => $p, 'room' => $r, 'bed' => $b, 'owner' => $o] = mktSetup();
    $t1 = mktTenant(); $t2 = mktTenant();
    $req1 = mktRequest(['user_id' => $t1->id, 'property_id' => $p->id, 'room_id' => $r->id, 'bed_id' => $b->id, 'house_owner_id' => $o->id], 'RR-DB-001');
    $req2 = mktRequest(['user_id' => $t2->id, 'property_id' => $p->id, 'room_id' => $r->id, 'bed_id' => $b->id, 'house_owner_id' => $o->id], 'RR-DB-002');
    $this->actingAs($ou)->post(route('owner.rental-requests.approve', $req1));
    $this->actingAs($ou)->post(route('owner.rental-requests.approve', $req2))->assertStatus(500);
});

// ---------------------------------------------------------------------------
// Privacy
// ---------------------------------------------------------------------------

test('super admin cannot access owner protected properties without privacy session', function () {
    $admin = User::create([
        'name' => 'Admin', 'email' => 'sadmin@t.test',
        'password' => 'password', 'role' => 'super_admin', 'status' => 'active',
    ]);
    ['owner' => $o] = mktSetup();
    $this->actingAs($admin)->get(route('admin.protected.properties', $o))->assertForbidden();
});
