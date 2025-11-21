<?php

declare(strict_types=1);

namespace App\Actions\Groups;

use App\Models\Group;
use Illuminate\Support\Facades\DB;

final readonly class UpdateGroupAction
{
    /**
     * Execute the action.
     */
    public function handle(Group $group, array $data): Group
    {
        return DB::transaction(function () use ($group, $data): Group {
            $group->update([
                'name' => $data['name'] ?? $group->name,
                'description' => $data['description'] ?? $group->description,
                'settings' => $data['settings'] ?? $group->settings,
            ]);

            return $group->fresh();
        });
    }
}
