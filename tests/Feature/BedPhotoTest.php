<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('owner can upload and replace an individual bed photo', function () {
    Storage::fake('public');
    [$ownerUser, $owner] = makeOwner('bed-photo');
    $property = makeProperty($owner);
    $room = makeRoom($property);
    $bed = makeBed($room);

    $this->actingAs($ownerUser)
        ->from(route('owner.rooms.show', $room))
        ->followingRedirects()
        ->post(route('owner.beds.photo.store', $bed), [
            'photo' => UploadedFile::fake()->image('bed-photo.jpg'),
        ])
        ->assertOk()
        ->assertSee("Photo for Bed {$bed->bed_number} updated.");

    $bed->refresh();
    $firstPath = $bed->photo;
    expect($firstPath)->toStartWith("beds/{$bed->id}/");
    Storage::disk('public')->assertExists($firstPath);

    $this->actingAs($ownerUser)
        ->post(route('owner.beds.photo.store', $bed), [
            'photo' => UploadedFile::fake()->image('replacement.png'),
        ])
        ->assertRedirect();

    $bed->refresh();
    expect($bed->photo)->not->toBe($firstPath);
    Storage::disk('public')->assertMissing($firstPath);
    Storage::disk('public')->assertExists($bed->photo);
});

test('owner cannot upload a photo to another owners bed', function () {
    Storage::fake('public');
    [$ownerUser] = makeOwner('bed-photo-a');
    [, $otherOwner] = makeOwner('bed-photo-b');
    $bed = makeBed(makeRoom(makeProperty($otherOwner)));

    $this->actingAs($ownerUser)
        ->post(route('owner.beds.photo.store', $bed), [
            'photo' => UploadedFile::fake()->image('unauthorized.jpg'),
        ])
        ->assertForbidden();

    expect($bed->fresh()->photo)->toBeNull();
    expect(Storage::disk('public')->allFiles())->toBeEmpty();
});

test('bed photos appear on owner and public bed cards with a missing-photo fallback', function () {
    Storage::fake('public');
    [$ownerUser, $owner] = makeOwner('bed-photo-render');
    $property = makeProperty($owner);
    $property->update(['is_listed' => true]);
    $room = makeRoom($property);
    $bed = makeBed($room, ['photo' => 'beds/sample/bed.jpg']);
    Storage::disk('public')->put($bed->photo, 'image');

    $this->actingAs($ownerUser)
        ->get(route('owner.rooms.show', $room))
        ->assertOk()
        ->assertSee('storage/'.$bed->photo)
        ->assertSee('Available')
        ->assertSee('Choose image');

    $this->actingAs($ownerUser)
        ->get(route('owner.properties.show', $property))
        ->assertOk()
        ->assertSee('storage/'.$bed->photo)
        ->assertSee('Bed '.$bed->bed_number)
        ->assertSee('w-24 shrink-0 aspect-[16/9]', false);

    $this->get(route('houses.room', [$property, $room]))
        ->assertOk()
        ->assertSee('storage/'.$bed->photo)
        ->assertSee('Request to Rent');

    $bed->update(['photo' => null]);
    $this->actingAs($ownerUser)
        ->get(route('owner.properties.show', $property))
        ->assertOk()
        ->assertSee('No bed photo available');

    $this->get(route('houses.room', [$property, $room]))
        ->assertOk()
        ->assertSee('No photo available')
        ->assertSee($bed->display_name)
        ->assertSee('Request to Rent');
});

test('bed photo upload rejects unsupported image types', function () {
    Storage::fake('public');
    [$ownerUser, $owner] = makeOwner('bed-photo-invalid');
    $bed = makeBed(makeRoom(makeProperty($owner)));

    $this->actingAs($ownerUser)
        ->from(route('owner.rooms.show', $bed->room))
        ->post(route('owner.beds.photo.store', $bed), [
            'photo' => UploadedFile::fake()->create('bed.gif', 10, 'image/gif'),
        ])
        ->assertRedirect(route('owner.rooms.show', $bed->room))
        ->assertSessionHasErrors('photo');

    expect($bed->fresh()->photo)->toBeNull();
    expect(Storage::disk('public')->allFiles())->toBeEmpty();
});

test('bed photo upload rejects images larger than four megabytes', function () {
    Storage::fake('public');
    [$ownerUser, $owner] = makeOwner('bed-photo-size');
    $bed = makeBed(makeRoom(makeProperty($owner)));

    $this->actingAs($ownerUser)
        ->from(route('owner.rooms.show', $bed->room))
        ->post(route('owner.beds.photo.store', $bed), [
            'photo' => UploadedFile::fake()->image('large-bed.jpg')->size(4097),
        ])
        ->assertRedirect(route('owner.rooms.show', $bed->room))
        ->assertSessionHasErrors('photo');

    expect($bed->fresh()->photo)->toBeNull();
    expect(Storage::disk('public')->allFiles())->toBeEmpty();
});
