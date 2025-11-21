<?php

declare(strict_types=1);

namespace App\Actions\Groups;

use App\Models\Group;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final readonly class LeaveGroupAction
{
    /**
     * Execute the action.
     */
    public function handle(Group $group, User $user): void
    {
        DB::transaction(function () use ($group, $user): void {
            // Don't allow the owner to leave their own group
            if ($group->owner_id === $user->id) {
                throw new RuntimeException('The group owner cannot leave the group. Transfer ownership first.');
            }

            $group->members()->detach($user->id);
        });
    }
}
