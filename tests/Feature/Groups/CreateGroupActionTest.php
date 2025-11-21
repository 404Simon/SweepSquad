<?php

declare(strict_types=1);

use App\Actions\Groups\CreateGroupAction;
use App\GroupRole;
use App\Models\Group;
use App\Models\User;

test('creates a group with owner', function () {
    $owner = User::factory()->create();
    $action = new CreateGroupAction;

    $group = $action->handle($owner, 'Test Group', 'Test Description');

    expect($group)->toBeInstanceOf(Group::class);
    expect($group->name)->toBe('Test Group');
    expect($group->description)->toBe('Test Description');
    expect($group->owner_id)->toBe($owner->id);
});

test('adds owner as group member with owner role', function () {
    $owner = User::factory()->create();
    $action = new CreateGroupAction;

    $group = $action->handle($owner, 'Test Group');

    $group->load('members');

    expect($group->members)->toHaveCount(1);
    expect($group->members->first()->id)->toBe($owner->id);
    expect($group->members->first()->pivot->role)->toBe(GroupRole::Owner);
});

test('creates group without description', function () {
    $owner = User::factory()->create();
    $action = new CreateGroupAction;

    $group = $action->handle($owner, 'Test Group');

    expect($group)->toBeInstanceOf(Group::class);
    expect($group->name)->toBe('Test Group');
    expect($group->description)->toBeNull();
});

test('generates uuid on group creation', function () {
    $owner = User::factory()->create();
    $action = new CreateGroupAction;

    $group = $action->handle($owner, 'Test Group');

    expect($group->uuid)->not->toBeNull();
    expect((string) $group->uuid)->toBeString();
});
