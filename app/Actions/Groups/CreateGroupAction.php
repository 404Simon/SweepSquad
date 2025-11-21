<?php

declare(strict_types=1);

namespace App\Actions\Groups;

use App\GroupRole;
use App\Models\Group;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final readonly class CreateGroupAction
{
    /**
     * Execute the action.
     */
    public function handle(User $owner, string $name, ?string $description = null): Group
    {
        return DB::transaction(function () use ($owner, $name, $description): Group {
            $group = Group::query()->create([
                'name' => $name,
                'description' => $description,
                'owner_id' => $owner->id,
            ]);

            $group->members()->attach($owner->id, [
                'role' => GroupRole::Owner,
                'joined_at' => now(),
            ]);

            return $group;
        });
    }
}
