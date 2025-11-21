<?php

use App\Actions\Groups\DeleteGroupAction;
use App\Actions\Groups\LeaveGroupAction;
use App\Actions\Groups\RemoveGroupMemberAction;
use App\GroupRole;
use App\Models\Group;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Locked;
use Livewire\Volt\Component;

use function Livewire\Volt\{layout};

layout('components.layouts.app');

new class extends Component {
    #[Locked]
    public Group $group;

    public function mount($id): void
    {
        $this->group = Group::query()
            ->with(['owner', 'members', 'cleaningItems'])
            ->withCount('members')
            ->findOrFail($id);

        // Check if user is a member
        if (!$this->group->members->contains(Auth::id())) {
            abort(403, 'You are not a member of this group.');
        }
    }

    public function deleteGroup(DeleteGroupAction $action): void
    {
        if ($this->group->owner_id !== Auth::id()) {
            abort(403, 'Only the group owner can delete the group.');
        }

        $action->handle($this->group);

        session()->flash('success', 'Group deleted successfully.');

        $this->redirect(route('groups.index'), navigate: true);
    }

    public function leaveGroup(LeaveGroupAction $action): void
    {
        try {
            $action->handle($this->group, Auth::user());

            session()->flash('success', 'You have left the group.');

            $this->redirect(route('groups.index'), navigate: true);
        } catch (\RuntimeException $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function removeMember(int $userId, RemoveGroupMemberAction $action): void
    {
        // Only owner and admins can remove members
        $currentMember = $this->group->members->firstWhere('id', Auth::id());
        if (!$currentMember || !in_array($currentMember->pivot->role, [GroupRole::Owner, GroupRole::Admin])) {
            abort(403, 'You do not have permission to remove members.');
        }

        $userToRemove = $this->group->members->firstWhere('id', $userId);
        if (!$userToRemove) {
            return;
        }

        $action->handle($this->group, $userToRemove);

        session()->flash('success', 'Member removed successfully.');

        $this->redirect(route('groups.show', $this->group), navigate: true);
    }

    public function with(): array
    {
        $currentMember = $this->group->members->firstWhere('id', Auth::id());
        $isOwner = $this->group->owner_id === Auth::id();
        $isAdmin = $currentMember && $currentMember->pivot->role === GroupRole::Admin;

        return [
            'isOwner' => $isOwner,
            'isAdmin' => $isAdmin,
            'canManageMembers' => $isOwner || $isAdmin,
        ];
    }
}; ?>

<div>
    <div class="max-w-6xl mx-auto px-4 py-8">
        @if(session('success'))
            <flux:callout variant="success" class="mb-6">
                {{ session('success') }}
            </flux:callout>
        @endif

        @if(session('error'))
            <flux:callout variant="danger" class="mb-6">
                {{ session('error') }}
            </flux:callout>
        @endif

        <div class="flex justify-between items-start mb-6">
            <div>
                <flux:heading size="lg">{{ $group->name }}</flux:heading>
                @if($group->description)
                    <flux:text class="mt-2">{{ $group->description }}</flux:text>
                @endif
                <flux:text class="text-sm text-zinc-500 mt-2">
                    {{ $group->members_count }} {{ Str::plural('member', $group->members_count) }}
                </flux:text>
            </div>

            <div class="flex gap-2">
                @if($isOwner)
                    <flux:button
                        variant="primary"
                        wire:navigate
                        href="{{ route('groups.edit', $group) }}"
                    >
                        Edit Group
                    </flux:button>

                    <flux:modal.trigger name="confirm-delete-group">
                        <flux:button
                            variant="danger"
                            x-on:click.prevent="$dispatch('open-modal', 'confirm-delete-group')"
                        >
                            Delete Group
                        </flux:button>
                    </flux:modal.trigger>
                @else
                    <flux:modal.trigger name="confirm-leave-group">
                        <flux:button
                            variant="danger"
                            x-on:click.prevent="$dispatch('open-modal', 'confirm-leave-group')"
                        >
                            Leave Group
                        </flux:button>
                    </flux:modal.trigger>
                @endif
            </div>
        </div>

        <div class="grid gap-6 md:grid-cols-2">
            <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-6">
                <flux:heading size="base" class="mb-4">Members</flux:heading>

                <div class="space-y-3">
                    @foreach($group->members as $member)
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-zinc-200 dark:bg-zinc-700 flex items-center justify-center">
                                    <flux:text class="font-semibold">{{ $member->initials() }}</flux:text>
                                </div>
                                <div>
                                    <flux:text class="font-medium">{{ $member->name }}</flux:text>
                                    <flux:text class="text-sm text-zinc-500">{{ ucfirst($member->pivot->role->value) }}</flux:text>
                                </div>
                            </div>

                            @if($canManageMembers && $member->id !== $group->owner_id && $member->id !== Auth::id())
                                <flux:button
                                    variant="ghost"
                                    size="sm"
                                    wire:click="removeMember({{ $member->id }})"
                                    wire:confirm="Are you sure you want to remove {{ $member->name }} from this group?"
                                >
                                    Remove
                                </flux:button>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-6">
                <flux:heading size="base" class="mb-4">Cleaning Items</flux:heading>

                @if($group->cleaningItems->isNotEmpty())
                    <div class="space-y-2">
                        @foreach($group->cleaningItems->take(5) as $item)
                            <div class="flex items-center justify-between py-2">
                                <flux:text>{{ $item->name }}</flux:text>
                            </div>
                        @endforeach
                    </div>

                    @if($group->cleaningItems->count() > 5)
                        <flux:text class="text-sm text-zinc-500 mt-4">
                            And {{ $group->cleaningItems->count() - 5 }} more items...
                        </flux:text>
                    @endif
                @else
                    <flux:text class="text-zinc-500">No cleaning items yet.</flux:text>
                @endif
            </div>
        </div>
    </div>

    <!-- Delete Group Confirmation Modal -->
    <flux:modal name="confirm-delete-group" focusable class="max-w-lg">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Delete {{ $group->name }}?</flux:heading>
                <flux:subheading>
                    Are you sure you want to delete this group? This action cannot be undone. All group data, including members, cleaning items, and logs will be permanently removed.
                </flux:subheading>
            </div>

            <div class="flex justify-end space-x-2">
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>

                <flux:button variant="danger" wire:click="deleteGroup">
                    Delete
                </flux:button>
            </div>
        </div>
    </flux:modal>

    <!-- Leave Group Confirmation Modal -->
    <flux:modal name="confirm-leave-group" focusable class="max-w-lg">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Leave {{ $group->name }}?</flux:heading>
                <flux:subheading>
                    Are you sure you want to leave this group? You will lose access to all group data and will need to be re-invited to rejoin.
                </flux:subheading>
            </div>

            <div class="flex justify-end space-x-2">
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>

                <flux:button variant="danger" wire:click="leaveGroup">
                    Leave
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
