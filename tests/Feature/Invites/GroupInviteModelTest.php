<?php

declare(strict_types=1);

use App\InviteType;
use App\Models\Group;
use App\Models\GroupInvite;
use App\Models\User;

test('valid scope returns only valid invites', function () {
    $group = Group::factory()->create();
    $creator = User::factory()->create();

    // Create valid invite
    GroupInvite::factory()->create([
        'group_id' => $group->id,
        'created_by' => $creator->id,
        'type' => InviteType::Permanent,
        'expires_at' => null,
        'used_at' => null,
    ]);

    // Create expired invite
    GroupInvite::factory()->create([
        'group_id' => $group->id,
        'created_by' => $creator->id,
        'type' => InviteType::TimeLimited,
        'expires_at' => now()->subDay(),
        'used_at' => null,
    ]);

    // Create used invite
    GroupInvite::factory()->create([
        'group_id' => $group->id,
        'created_by' => $creator->id,
        'type' => InviteType::SingleUse,
        'expires_at' => null,
        'used_at' => now(),
    ]);

    $validInvites = GroupInvite::valid()->get();

    expect($validInvites)->toHaveCount(1);
    expect($validInvites->first()->isValid())->toBeTrue();
});

test('expired scope returns only expired invites', function () {
    $group = Group::factory()->create();
    $creator = User::factory()->create();

    // Create valid invite
    GroupInvite::factory()->create([
        'group_id' => $group->id,
        'created_by' => $creator->id,
        'expires_at' => now()->addDay(),
    ]);

    // Create expired invite
    GroupInvite::factory()->create([
        'group_id' => $group->id,
        'created_by' => $creator->id,
        'expires_at' => now()->subDay(),
    ]);

    $expiredInvites = GroupInvite::expired()->get();

    expect($expiredInvites)->toHaveCount(1);
});

test('unused scope returns only unused invites', function () {
    $group = Group::factory()->create();
    $creator = User::factory()->create();
    $user = User::factory()->create();

    // Create unused invite
    GroupInvite::factory()->create([
        'group_id' => $group->id,
        'created_by' => $creator->id,
        'used_at' => null,
    ]);

    // Create used invite
    GroupInvite::factory()->create([
        'group_id' => $group->id,
        'created_by' => $creator->id,
        'used_at' => now(),
        'used_by' => $user->id,
    ]);

    $unusedInvites = GroupInvite::unused()->get();

    expect($unusedInvites)->toHaveCount(1);
    expect($unusedInvites->first()->used_at)->toBeNull();
});

test('isValid returns true for valid invite', function () {
    $invite = GroupInvite::factory()->create([
        'type' => InviteType::Permanent,
        'expires_at' => null,
        'used_at' => null,
    ]);

    expect($invite->isValid())->toBeTrue();
});

test('isValid returns false for used invite', function () {
    $invite = GroupInvite::factory()->create([
        'type' => InviteType::SingleUse,
        'expires_at' => null,
        'used_at' => now(),
    ]);

    expect($invite->isValid())->toBeFalse();
});

test('isValid returns false for expired invite', function () {
    $invite = GroupInvite::factory()->create([
        'type' => InviteType::TimeLimited,
        'expires_at' => now()->subDay(),
        'used_at' => null,
    ]);

    expect($invite->isValid())->toBeFalse();
});

test('markAsUsed updates invite correctly', function () {
    $user = User::factory()->create();
    $invite = GroupInvite::factory()->create([
        'used_at' => null,
        'used_by' => null,
    ]);

    $invite->markAsUsed($user);

    $invite->refresh();

    expect($invite->used_at)->not->toBeNull();
    expect($invite->used_by)->toBe($user->id);
});
