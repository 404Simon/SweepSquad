<?php

declare(strict_types=1);

use App\Jobs\CleanupExpiredInvites;
use App\Models\Group;
use App\Models\GroupInvite;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('deletes expired invites', function () {
    $group = Group::factory()->create();
    $creator = User::factory()->create();

    // Create an expired invite
    $expiredInvite = GroupInvite::factory()->create([
        'group_id' => $group->id,
        'created_by' => $creator->id,
        'expires_at' => now()->subDay(),
        'used_at' => null,
    ]);

    // Run the job
    (new CleanupExpiredInvites)->handle();

    // Invite should be deleted
    expect(GroupInvite::find($expiredInvite->id))->toBeNull();
});

test('does not delete valid invites', function () {
    $group = Group::factory()->create();
    $creator = User::factory()->create();

    // Create a valid invite (expires in the future)
    $validInvite = GroupInvite::factory()->create([
        'group_id' => $group->id,
        'created_by' => $creator->id,
        'expires_at' => now()->addWeek(),
        'used_at' => null,
    ]);

    // Run the job
    (new CleanupExpiredInvites)->handle();

    // Invite should still exist
    expect(GroupInvite::find($validInvite->id))->not->toBeNull();
});

test('does not delete permanent invites', function () {
    $group = Group::factory()->create();
    $creator = User::factory()->create();

    // Create a permanent invite (no expiration)
    $permanentInvite = GroupInvite::factory()->create([
        'group_id' => $group->id,
        'created_by' => $creator->id,
        'expires_at' => null,
        'used_at' => null,
    ]);

    // Run the job
    (new CleanupExpiredInvites)->handle();

    // Invite should still exist
    expect(GroupInvite::find($permanentInvite->id))->not->toBeNull();
});

test('does not delete used invites even if expired', function () {
    $group = Group::factory()->create();
    $creator = User::factory()->create();
    $user = User::factory()->create();

    // Create an expired but used invite
    $usedExpiredInvite = GroupInvite::factory()->create([
        'group_id' => $group->id,
        'created_by' => $creator->id,
        'expires_at' => now()->subDay(),
        'used_by' => $user->id,
        'used_at' => now()->subHour(),
    ]);

    // Run the job
    (new CleanupExpiredInvites)->handle();

    // Invite should still exist (for audit trail)
    expect(GroupInvite::find($usedExpiredInvite->id))->not->toBeNull();
});

test('processes multiple invites correctly', function () {
    $group = Group::factory()->create();
    $creator = User::factory()->create();

    // Create multiple invites with different statuses
    $expired1 = GroupInvite::factory()->create([
        'group_id' => $group->id,
        'created_by' => $creator->id,
        'expires_at' => now()->subDays(5),
        'used_at' => null,
    ]);

    $expired2 = GroupInvite::factory()->create([
        'group_id' => $group->id,
        'created_by' => $creator->id,
        'expires_at' => now()->subHours(2),
        'used_at' => null,
    ]);

    $valid = GroupInvite::factory()->create([
        'group_id' => $group->id,
        'created_by' => $creator->id,
        'expires_at' => now()->addWeek(),
        'used_at' => null,
    ]);

    $permanent = GroupInvite::factory()->create([
        'group_id' => $group->id,
        'created_by' => $creator->id,
        'expires_at' => null,
        'used_at' => null,
    ]);

    // Run the job
    (new CleanupExpiredInvites)->handle();

    // Check results
    expect(GroupInvite::find($expired1->id))->toBeNull();
    expect(GroupInvite::find($expired2->id))->toBeNull();
    expect(GroupInvite::find($valid->id))->not->toBeNull();
    expect(GroupInvite::find($permanent->id))->not->toBeNull();
});
