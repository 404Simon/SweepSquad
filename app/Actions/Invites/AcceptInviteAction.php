<?php

declare(strict_types=1);

namespace App\Actions\Invites;

use App\Actions\Groups\AddGroupMemberAction;
use App\GroupRole;
use App\Models\GroupInvite;
use App\Models\GroupMember;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final readonly class AcceptInviteAction
{
    public function __construct(
        private AddGroupMemberAction $addGroupMemberAction,
    ) {}

    /**
     * Execute the action.
     */
    public function handle(GroupInvite $invite, User $user): GroupMember
    {
        return DB::transaction(function () use ($invite, $user): GroupMember {
            // Verify invite is valid
            if (! $invite->isValid()) {
                throw new InvalidArgumentException('This invite is no longer valid.');
            }

            // Verify user is not already a member
            $existingMember = $invite->group->groupMemberships()
                ->where('user_id', $user->id)
                ->first();

            if ($existingMember !== null) {
                throw new InvalidArgumentException('You are already a member of this group.');
            }

            // Mark invite as used
            $invite->markAsUsed($user);

            // Add user to group
            return $this->addGroupMemberAction->handle(
                $invite->group,
                $user,
                GroupRole::Member
            );
        });
    }
}
