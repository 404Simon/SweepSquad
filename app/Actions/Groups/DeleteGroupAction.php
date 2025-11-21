<?php

declare(strict_types=1);

namespace App\Actions\Groups;

use App\Models\Group;
use Illuminate\Support\Facades\DB;

final readonly class DeleteGroupAction
{
    /**
     * Execute the action.
     */
    public function handle(Group $group): void
    {
        DB::transaction(function () use ($group): void {
            $group->delete();
        });
    }
}
