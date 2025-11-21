<?php

declare(strict_types=1);

namespace App\Actions\Invites;

use App\Models\GroupInvite;

final readonly class RevokeInviteAction
{
    /**
     * Execute the action.
     */
    public function handle(GroupInvite $invite): bool
    {
        return $invite->delete();
    }
}
