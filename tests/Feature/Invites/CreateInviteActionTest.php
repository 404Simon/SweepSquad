<?php

declare(strict_types=1);

use App\Actions\Invites\CreateInviteAction;
use App\InviteType;
use App\Models\Group;
use App\Models\GroupInvite;
use App\Models\User;

test('creates permanent invite', function () {
    $group = Group::factory()->create();
    $creator = User::factory()->create();
    $action = new CreateInviteAction;

    $invite = $action->handle($group, $creator, InviteType::Permanent);

    expect($invite)->toBeInstanceOf(GroupInvite::class);
    expect($invite->group_id)->toBe($group->id);
    expect($invite->created_by)->toBe($creator->id);
    expect($invite->type)->toBe(InviteType::Permanent);
    expect($invite->expires_at)->toBeNull();
    expect($invite->uuid)->not->toBeNull();
});

test('creates single use invite', function () {
    $group = Group::factory()->create();
    $creator = User::factory()->create();
    $action = new CreateInviteAction;

    $invite = $action->handle($group, $creator, InviteType::SingleUse);

    expect($invite)->toBeInstanceOf(GroupInvite::class);
    expect($invite->type)->toBe(InviteType::SingleUse);
    expect($invite->expires_at)->toBeNull();
});

test('creates time limited invite', function () {
    $group = Group::factory()->create();
    $creator = User::factory()->create();
    $action = new CreateInviteAction;
    $expiresAt = now()->addDays(7);

    $invite = $action->handle($group, $creator, InviteType::TimeLimited, $expiresAt);

    expect($invite)->toBeInstanceOf(GroupInvite::class);
    expect($invite->type)->toBe(InviteType::TimeLimited);
    expect($invite->expires_at)->not->toBeNull();
    expect($invite->expires_at->toDateString())->toBe($expiresAt->toDateString());
});

test('generates unique uuid for each invite', function () {
    $group = Group::factory()->create();
    $creator = User::factory()->create();
    $action = new CreateInviteAction;

    $invite1 = $action->handle($group, $creator, InviteType::Permanent);
    $invite2 = $action->handle($group, $creator, InviteType::Permanent);

    expect($invite1->uuid)->not->toBe($invite2->uuid);
});
