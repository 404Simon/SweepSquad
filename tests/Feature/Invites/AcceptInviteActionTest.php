<?php

declare(strict_types=1);

use App\Actions\Invites\AcceptInviteAction;
use App\Actions\Invites\CreateInviteAction;
use App\GroupRole;
use App\InviteType;
use App\Models\Group;
use App\Models\GroupMember;
use App\Models\User;

test('accepts valid invite and adds user to group', function () {
    $group = Group::factory()->create();
    $creator = User::factory()->create();
    $user = User::factory()->create();

    $createAction = new CreateInviteAction;
    $invite = $createAction->handle($group, $creator, InviteType::Permanent);

    $acceptAction = new AcceptInviteAction(new App\Actions\Groups\AddGroupMemberAction);
    $member = $acceptAction->handle($invite, $user);

    expect($member)->toBeInstanceOf(GroupMember::class);
    expect($member->user_id)->toBe($user->id);
    expect($member->group_id)->toBe($group->id);
    expect($member->role)->toBe(GroupRole::Member);
});

test('marks invite as used after acceptance', function () {
    $group = Group::factory()->create();
    $creator = User::factory()->create();
    $user = User::factory()->create();

    $createAction = new CreateInviteAction;
    $invite = $createAction->handle($group, $creator, InviteType::SingleUse);

    $acceptAction = new AcceptInviteAction(new App\Actions\Groups\AddGroupMemberAction);
    $acceptAction->handle($invite, $user);

    $invite->refresh();

    expect($invite->used_at)->not->toBeNull();
    expect($invite->used_by)->toBe($user->id);
    expect($invite->isValid())->toBeFalse();
});

test('prevents accepting expired invite', function () {
    $group = Group::factory()->create();
    $creator = User::factory()->create();
    $user = User::factory()->create();

    $createAction = new CreateInviteAction;
    $invite = $createAction->handle(
        $group,
        $creator,
        InviteType::TimeLimited,
        now()->subDay()
    );

    $acceptAction = new AcceptInviteAction(new App\Actions\Groups\AddGroupMemberAction);

    expect(fn () => $acceptAction->handle($invite, $user))
        ->toThrow(InvalidArgumentException::class, 'This invite is no longer valid.');
});

test('prevents accepting already used invite', function () {
    $group = Group::factory()->create();
    $creator = User::factory()->create();
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $createAction = new CreateInviteAction;
    $invite = $createAction->handle($group, $creator, InviteType::SingleUse);

    $acceptAction = new AcceptInviteAction(new App\Actions\Groups\AddGroupMemberAction);
    $acceptAction->handle($invite, $user1);

    $invite->refresh();

    expect(fn () => $acceptAction->handle($invite, $user2))
        ->toThrow(InvalidArgumentException::class, 'This invite is no longer valid.');
});

test('prevents user from joining group twice', function () {
    $group = Group::factory()->create();
    $creator = User::factory()->create();
    $user = User::factory()->create();

    // Add user to group first
    $group->members()->attach($user->id, [
        'role' => GroupRole::Member,
        'joined_at' => now(),
    ]);

    $createAction = new CreateInviteAction;
    $invite = $createAction->handle($group, $creator, InviteType::Permanent);

    $acceptAction = new AcceptInviteAction(new App\Actions\Groups\AddGroupMemberAction);

    expect(fn () => $acceptAction->handle($invite, $user))
        ->toThrow(InvalidArgumentException::class, 'You are already a member of this group.');
});
