<?php

use App\Actions\CleaningItems\MarkAsCleanedAction;
use App\Actions\Groups\DeleteGroupAction;
use App\Actions\Groups\LeaveGroupAction;
use App\Actions\Groups\RemoveGroupMemberAction;
use App\GroupRole;
use App\Models\CleaningItem;
use App\Models\Group;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Locked;
use Livewire\Volt\Component;

use function Livewire\Volt\{layout};

layout('components.layouts.app');

new class extends Component {
    #[Locked]
    public Group $group;

    public string $filter = 'all';
    public string $sortBy = 'dirtiness';

    public function mount($id): void
    {
        $this->group = Group::query()
            ->with(['owner', 'members'])
            ->withCount('members')
            ->findOrFail($id);

        // Check if user is a member
        if (!$this->group->members->contains(Auth::id())) {
            abort(403, 'You are not a member of this group.');
        }
    }

    public function getMembersCountProperty(): int
    {
        return $this->group->members->count();
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

    public function markAsCleaned(int $itemId, MarkAsCleanedAction $action): void
    {
        $item = CleaningItem::query()
            ->where('group_id', $this->group->id)
            ->findOrFail($itemId);

        $action->handle($item, Auth::user());

        $this->dispatch('item-cleaned');
    }

    public function setFilter(string $filter): void
    {
        $this->filter = $filter;
    }

    public function setSortBy(string $sortBy): void
    {
        $this->sortBy = $sortBy;
    }

    public function getItemsProperty(): \Illuminate\Support\Collection
    {
        $query = $this->group->cleaningItems()
            ->with(['lastCleanedByUser', 'children.lastCleanedByUser', 'children.children.lastCleanedByUser'])
            ->roots();

        // Apply filters
        match ($this->filter) {
            'overdue' => $query->overdue(),
            'needs_attention' => $query->needsAttention(),
            'clean' => $query->freshlyClean(),
            default => null,
        };

        // Get items and calculate dirtiness for sorting
        $items = $query->get();

        // Apply sorting
        return match ($this->sortBy) {
            'dirtiness' => $items->sortByDesc(fn($item) => $item->dirtiness_percentage),
            'coins' => $items->sortByDesc(fn($item) => $item->coins_available),
            'last_cleaned' => $items->sortBy(fn($item) => $item->last_cleaned_at?->timestamp ?? 0),
            'name' => $items->sortBy('name'),
            default => $items,
        };
    }

    public function getStatsProperty(): array
    {
        $allItems = $this->group->cleaningItems;

        return [
            'total' => $allItems->count(),
            'overdue' => $allItems->filter(fn($item) => $item->is_overdue)->count(),
            'needs_attention' => $allItems->filter(fn($item) => $item->needs_attention && !$item->is_overdue)->count(),
            'clean' => $allItems->filter(fn($item) => $item->is_freshly_clean)->count(),
        ];
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
            'items' => $this->items,
            'stats' => $this->stats,
        ];
    }
}; ?>

<div wire:poll.60s>
    <div class="max-w-7xl mx-auto px-4 py-8">
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

        {{-- Header Section --}}
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 mb-6">
            <div class="flex-1">
                <div class="flex items-center gap-3 mb-2">
                    <flux:heading size="lg">{{ $group->name }}</flux:heading>
                    @if($isOwner)
                        <flux:badge variant="primary">Owner</flux:badge>
                    @elseif($isAdmin)
                        <flux:badge variant="primary">Admin</flux:badge>
                    @endif
                </div>
                @if($group->description)
                    <flux:text class="mt-2 text-zinc-600 dark:text-zinc-400">{{ $group->description }}</flux:text>
                @endif
                <flux:text class="text-sm text-zinc-500 mt-2">
                    {{ $this->members_count }} {{ Str::plural('member', $this->members_count) }}
                </flux:text>
            </div>

            <div class="flex gap-2">
                @if($isOwner || $isAdmin)
                    <flux:button
                        variant="primary"
                        wire:navigate
                        href="{{ route('cleaning-items.create', ['group' => $group->id]) }}"
                    >
                        Add Item
                    </flux:button>
                @endif

                @if($isOwner)
                    <flux:button
                        variant="ghost"
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
                            Delete
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

        {{-- Stats Section --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-4">
                <flux:text class="text-sm text-zinc-500 mb-1">Total Items</flux:text>
                <flux:heading size="lg">{{ $stats['total'] }}</flux:heading>
            </div>
            <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-4">
                <flux:text class="text-sm text-red-500 dark:text-red-400 mb-1">Overdue</flux:text>
                <flux:heading size="lg" class="text-red-600 dark:text-red-400">{{ $stats['overdue'] }}</flux:heading>
            </div>
            <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-4">
                <flux:text class="text-sm text-orange-500 dark:text-orange-400 mb-1">Needs Attention</flux:text>
                <flux:heading size="lg" class="text-orange-600 dark:text-orange-400">{{ $stats['needs_attention'] }}</flux:heading>
            </div>
            <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-4">
                <flux:text class="text-sm text-green-500 dark:text-green-400 mb-1">Clean</flux:text>
                <flux:heading size="lg" class="text-green-600 dark:text-green-400">{{ $stats['clean'] }}</flux:heading>
            </div>
        </div>

        {{-- Filters and Sorting --}}
        <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-4 mb-6">
            <div class="flex flex-col md:flex-row justify-between gap-4">
                <div class="flex gap-2 flex-wrap">
                    <flux:text class="text-sm font-medium mr-2 self-center">Show:</flux:text>
                    <flux:button
                        variant="{{ $filter === 'all' ? 'primary' : 'ghost' }}"
                        size="sm"
                        wire:click="setFilter('all')"
                    >
                        All
                    </flux:button>
                    <flux:button
                        variant="{{ $filter === 'overdue' ? 'primary' : 'ghost' }}"
                        size="sm"
                        wire:click="setFilter('overdue')"
                    >
                        Overdue
                    </flux:button>
                    <flux:button
                        variant="{{ $filter === 'needs_attention' ? 'primary' : 'ghost' }}"
                        size="sm"
                        wire:click="setFilter('needs_attention')"
                    >
                        Needs Attention
                    </flux:button>
                    <flux:button
                        variant="{{ $filter === 'clean' ? 'primary' : 'ghost' }}"
                        size="sm"
                        wire:click="setFilter('clean')"
                    >
                        Clean
                    </flux:button>
                </div>

                <div class="flex gap-2 flex-wrap">
                    <flux:text class="text-sm font-medium mr-2 self-center">Sort by:</flux:text>
                    <flux:button
                        variant="{{ $sortBy === 'dirtiness' ? 'primary' : 'ghost' }}"
                        size="sm"
                        wire:click="setSortBy('dirtiness')"
                    >
                        Dirtiness
                    </flux:button>
                    <flux:button
                        variant="{{ $sortBy === 'coins' ? 'primary' : 'ghost' }}"
                        size="sm"
                        wire:click="setSortBy('coins')"
                    >
                        Coins
                    </flux:button>
                    <flux:button
                        variant="{{ $sortBy === 'last_cleaned' ? 'primary' : 'ghost' }}"
                        size="sm"
                        wire:click="setSortBy('last_cleaned')"
                    >
                        Last Cleaned
                    </flux:button>
                    <flux:button
                        variant="{{ $sortBy === 'name' ? 'primary' : 'ghost' }}"
                        size="sm"
                        wire:click="setSortBy('name')"
                    >
                        Name
                    </flux:button>
                </div>
            </div>
        </div>

        {{-- Cleaning Items Tree --}}
        <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700">
            @if($items->isEmpty())
                <div class="p-8 text-center">
                    <flux:text class="text-zinc-500 mb-4">No cleaning items yet.</flux:text>
                    @if($isOwner || $isAdmin)
                        <flux:button
                            variant="primary"
                            wire:navigate
                            href="{{ route('cleaning-items.create', ['group' => $group->id]) }}"
                        >
                            Add Your First Item
                        </flux:button>
                    @endif
                </div>
            @else
                <div class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @foreach($items as $item)
                        @include('livewire.groups.partials.item-row', ['item' => $item, 'level' => 0])
                    @endforeach
                </div>
            @endif
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
