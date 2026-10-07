<?php

use App\Models\HouseOwner;
use App\Models\User;

test('admin login redirects to the admin dashboard', function () {
    $admin = makeAdmin();

    $this->post('/login', ['email' => $admin->email, 'password' => 'password'])
        ->assertRedirect(route('admin.dashboard'));

    $this->assertAuthenticatedAs($admin);
});

test('owner login redirects to the owner dashboard', function () {
    [$ownerUser] = makeOwner('login');

    $this->post('/login', ['email' => $ownerUser->email, 'password' => 'password'])
        ->assertRedirect(route('owner.dashboard'));

    $this->assertAuthenticatedAs($ownerUser);
});

test('tenant login redirects to the tenant dashboard', function () {
    [, $owner] = makeOwner('withtenant');
    $tenant = makeTenant($owner);

    $this->post('/login', ['email' => $tenant->user->email, 'password' => 'password'])
        ->assertRedirect(route('tenant.dashboard'));
});

test('invalid credentials are rejected', function () {
    $admin = makeAdmin();

    $this->from('/login')
        ->post('/login', ['email' => $admin->email, 'password' => 'wrong-password'])
        ->assertRedirect('/login')
        ->assertSessionHasErrors('email');

    $this->assertGuest();
});

test('suspended accounts cannot log in', function () {
    $admin = makeAdmin();
    $admin->update(['status' => 'suspended']);

    $this->from('/login')
        ->post('/login', ['email' => $admin->email, 'password' => 'password'])
        ->assertRedirect('/login')
        ->assertSessionHasErrors('email');

    $this->assertGuest();
});

test('guests are redirected to login from protected areas', function () {
    $this->get('/admin/dashboard')->assertRedirect('/login');
    $this->get('/owner/dashboard')->assertRedirect('/login');
    $this->get('/tenant/dashboard')->assertRedirect('/login');
});

test('owner registration creates a user and house owner workspace', function () {
    $this->post('/register/owner', [
        'name'          => 'New Fake Owner',
        'email'         => 'newowner@testing.test',
        'password'      => 'password123',
        'password_confirmation' => 'password123',
    ])->assertRedirect(route('owner.dashboard'));

    $this->assertAuthenticated();

    $user = User::where('email', 'newowner@testing.test')->first();
    expect($user)->not->toBeNull()
        ->and($user->role)->toBe(User::ROLE_OWNER)
        ->and(HouseOwner::where('user_id', $user->id)->exists())->toBeTrue();
});

test('logout ends the session', function () {
    $admin = makeAdmin();

    $this->actingAs($admin)->post('/logout')->assertRedirect('/login');
    $this->assertGuest();
});
