<?php

declare(strict_types=1);

use App\Achievement;
use App\Actions\Achievements\AwardAchievementAction;
use App\Actions\Achievements\CheckAchievementsAction;
use App\Models\CleaningItem;
use App\Models\CleaningLog;
use App\Models\Group;
use App\Models\User;
use App\Models\UserAchievement;

use function Pest\Laravel\actingAs;

test('can award achievement to user', function () {
    $user = User::factory()->create();
    $action = new AwardAchievementAction;

    $userAchievement = $action->handle($user, Achievement::FirstClean);

    expect($userAchievement)->toBeInstanceOf(UserAchievement::class)
        ->and($userAchievement->user_id)->toBe($user->id)
        ->and($userAchievement->achievement_code)->toBe(Achievement::FirstClean->value);
});

test('does not award duplicate achievements', function () {
    $user = User::factory()->create();
    $action = new AwardAchievementAction;

    $first = $action->handle($user, Achievement::FirstClean);
    $second = $action->handle($user, Achievement::FirstClean);

    expect($first)->toBeInstanceOf(UserAchievement::class)
        ->and($second)->toBeNull()
        ->and($user->achievements()->count())->toBe(1);
});

test('awards first clean achievement', function () {
    $user = User::factory()->create();
    $group = Group::factory()->create(['owner_id' => $user->id]);
    $item = CleaningItem::factory()->create(['group_id' => $group->id]);

    CleaningLog::factory()->create([
        'user_id' => $user->id,
        'cleaning_item_id' => $item->id,
        'group_id' => $group->id,
    ]);

    $action = new CheckAchievementsAction(new AwardAchievementAction);
    $awarded = $action->handle($user);

    expect($awarded)->toHaveCount(2) // FirstClean and SquadCreator
        ->and($user->hasAchievement(Achievement::FirstClean))->toBeTrue();
});

test('awards squad member achievement', function () {
    $user = User::factory()->create();
    $group = Group::factory()->create();

    $user->groups()->attach($group->id, [
        'role' => 'member',
        'joined_at' => now(),
    ]);

    $action = new CheckAchievementsAction(new AwardAchievementAction);
    $awarded = $action->handle($user);

    expect($user->hasAchievement(Achievement::SquadMember))->toBeTrue();
});

test('awards squad creator achievement', function () {
    $user = User::factory()->create();
    Group::factory()->create(['owner_id' => $user->id]);

    $action = new CheckAchievementsAction(new AwardAchievementAction);
    $awarded = $action->handle($user);

    expect($user->hasAchievement(Achievement::SquadCreator))->toBeTrue();
});

test('awards coin collector achievements', function () {
    $user = User::factory()->create(['total_coins' => 150]);

    $action = new CheckAchievementsAction(new AwardAchievementAction);
    $action->handle($user);

    expect($user->hasAchievement(Achievement::CoinCollector100))->toBeTrue()
        ->and($user->hasAchievement(Achievement::CoinCollector500))->toBeFalse();

    $user->update(['total_coins' => 600]);
    $action->handle($user);

    expect($user->hasAchievement(Achievement::CoinCollector500))->toBeTrue();
});

test('awards streak master achievements', function () {
    $user = User::factory()->create(['current_streak' => 8]);

    $action = new CheckAchievementsAction(new AwardAchievementAction);
    $action->handle($user);

    expect($user->hasAchievement(Achievement::StreakMaster7))->toBeTrue()
        ->and($user->hasAchievement(Achievement::StreakMaster14))->toBeFalse();

    $user->update(['current_streak' => 15]);
    $action->handle($user);

    expect($user->hasAchievement(Achievement::StreakMaster14))->toBeTrue();
});

test('awards team player achievement', function () {
    $user = User::factory()->create();
    $group = Group::factory()->create(['owner_id' => $user->id]);
    $item = CleaningItem::factory()->create(['group_id' => $group->id]);

    CleaningLog::factory()->count(10)->create([
        'user_id' => $user->id,
        'cleaning_item_id' => $item->id,
        'group_id' => $group->id,
    ]);

    $action = new CheckAchievementsAction(new AwardAchievementAction);
    $action->handle($user);

    expect($user->hasAchievement(Achievement::TeamPlayer))->toBeTrue();
});

test('awards room owner achievement', function () {
    $user = User::factory()->create();
    $group = Group::factory()->create(['owner_id' => $user->id]);
    $item = CleaningItem::factory()->create(['group_id' => $group->id]);

    CleaningLog::factory()->count(50)->create([
        'user_id' => $user->id,
        'cleaning_item_id' => $item->id,
        'group_id' => $group->id,
    ]);

    $action = new CheckAchievementsAction(new AwardAchievementAction);
    $action->handle($user);

    expect($user->hasAchievement(Achievement::RoomOwner))->toBeTrue();
});

test('awards perfectionist achievement', function () {
    $user = User::factory()->create();
    $group = Group::factory()->create(['owner_id' => $user->id]);
    $item = CleaningItem::factory()->create(['group_id' => $group->id]);

    CleaningLog::factory()->count(10)->create([
        'user_id' => $user->id,
        'cleaning_item_id' => $item->id,
        'group_id' => $group->id,
        'dirtiness_at_clean' => 100.0,
    ]);

    $action = new CheckAchievementsAction(new AwardAchievementAction);
    $action->handle($user);

    expect($user->hasAchievement(Achievement::Perfectionist))->toBeTrue();
});

test('user can view their stats page', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->get(route('profile.stats'))
        ->assertSuccessful()
        ->assertSee('Your Statistics');
});

test('user can view their achievements page', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->get(route('profile.achievements'))
        ->assertSuccessful()
        ->assertSee('Your Achievements');
});
