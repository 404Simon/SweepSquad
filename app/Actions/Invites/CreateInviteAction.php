<?php

declare(strict_types=1);

namespace App\Actions\Invites;

use App\InviteType;
use App\Models\Group;
use App\Models\GroupInvite;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

final readonly class CreateInviteAction
{
    /**
     * Execute the action.
     */
    public function handle(Group $group, User $creator, InviteType $type, ?CarbonImmutable $expiresAt = null): GroupInvite
    {
        return DB::transaction(fn (): GroupInvite => GroupInvite::create([
            'group_id' => $group->id,
            'created_by' => $creator->id,
            'type' => $type,
            'expires_at' => $expiresAt,
        ]));
    }
}
