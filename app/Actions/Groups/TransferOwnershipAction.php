<?php

declare(strict_types=1);

namespace App\Actions\Groups;

use App\GroupRole;
use App\Models\Group;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final readonly class TransferOwnershipAction
{
    /**
     * Execute the action.
     */
    public function handle(Group $group, User $newOwner): Group
    {
        return DB::transaction(function () use ($group, $newOwner): Group {
            $oldOwnerId = $group->owner_id;

            // Update the group's owner
            $group->update(['owner_id' => $newOwner->id]);

            // Update the old owner's role to Admin
            $group->members()->updateExistingPivot($oldOwnerId, [
                'role' => GroupRole::Admin,
            ]);

            // Update the new owner's role to Owner
            $group->members()->updateExistingPivot($newOwner->id, [
                'role' => GroupRole::Owner,
            ]);

            return $group->fresh();
        });
    }
}
