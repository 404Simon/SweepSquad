<?php

declare(strict_types=1);

namespace App\Actions\Groups;

use App\GroupRole;
use App\Models\Group;
use App\Models\GroupMember;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final readonly class AddGroupMemberAction
{
    /**
     * Execute the action.
     */
    public function handle(Group $group, User $user, GroupRole $role = GroupRole::Member): GroupMember
    {
        return DB::transaction(function () use ($group, $user, $role): GroupMember {
            $group->members()->attach($user->id, [
                'role' => $role,
                'joined_at' => now(),
            ]);

            return GroupMember::query()
                ->where('group_id', $group->id)
                ->where('user_id', $user->id)
                ->firstOrFail();
        });
    }
}
