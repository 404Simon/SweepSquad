<?php

declare(strict_types=1);

namespace App\Actions\Groups;

use App\Models\Group;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final readonly class RemoveGroupMemberAction
{
    /**
     * Execute the action.
     */
    public function handle(Group $group, User $user): void
    {
        DB::transaction(function () use ($group, $user): void {
            $group->members()->detach($user->id);
        });
    }
}
