<?php

use App\Models\CleaningItem;
use Livewire\Volt\Component;

use function Livewire\Volt\{layout, state, mount, computed};

layout('components.layouts.app');

state(['itemId', 'item']);

mount(function (int $id) {
    $this->itemId = $id;
    $this->item = CleaningItem::query()
        ->with(['children', 'lastCleanedByUser', 'parent'])
        ->findOrFail($id);
});

$children = computed(fn () => $this->item->children()->orderBy('order')->get());

$refreshItem = function () {
    $this->item = CleaningItem::query()
        ->with(['children', 'lastCleanedByUser', 'parent'])
        ->findOrFail($this->itemId);
};

new class extends Component {
    //
}; ?>

<div x-on:item-cleaned.window="$wire.call('refreshItem')">
    <div class="max-w-4xl mx-auto px-4 py-8">
        <div class="mb-6 flex items-center justify-between">
            <div>
                <flux:heading size="lg">{{ $item->name }}</flux:heading>
                @if ($item->parent)
                    <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">
                        Parent: {{ $item->parent->name }}
                    </flux:text>
                @endif
            </div>
            <flux:button
                variant="ghost"
                wire:navigate
                href="{{ route('cleaning-items.edit', $item->id) }}"
            >
                Edit
            </flux:button>
        </div>

        <div class="space-y-6">
            @if ($item->description)
                <div>
                    <flux:heading size="sm" class="mb-2">Description</flux:heading>
                    <flux:text>{{ $item->description }}</flux:text>
                </div>
            @endif

            <div class="grid grid-cols-2 gap-6">
                @if ($item->cleaning_frequency_hours)
                    <div>
                        <flux:heading size="sm" class="mb-2">Cleaning Frequency</flux:heading>
                        <flux:text>Every {{ $item->cleaning_frequency_hours }} hours</flux:text>
                    </div>

                    <div>
                        <flux:heading size="sm" class="mb-2">Dirtiness</flux:heading>
                        <flux:text>{{ round($item->dirtiness_percentage) }}%</flux:text>
                        @if ($item->is_overdue)
                            <flux:badge variant="danger" class="ml-2">Overdue</flux:badge>
                        @elseif ($item->needs_attention)
                            <flux:badge variant="warning" class="ml-2">Needs Attention</flux:badge>
                        @else
                            <flux:badge variant="success" class="ml-2">Clean</flux:badge>
                        @endif
                    </div>
                @endif

                <div>
                    <flux:heading size="sm" class="mb-2">Base Coin Reward</flux:heading>
                    <flux:text>{{ $item->base_coin_reward }} coins</flux:text>
                </div>

                @if ($item->cleaning_frequency_hours)
                    <div>
                        <flux:heading size="sm" class="mb-2">Current Coins Available</flux:heading>
                        <flux:text class="font-semibold">{{ $item->coins_available }} coins</flux:text>
                    </div>
                @endif
            </div>

            @if ($item->last_cleaned_at)
                <div>
                    <flux:heading size="sm" class="mb-2">Last Cleaned</flux:heading>
                    <flux:text>
                        {{ $item->last_cleaned_at->diffForHumans() }}
                        @if ($item->lastCleanedByUser)
                            by {{ $item->lastCleanedByUser->name }}
                        @endif
                    </flux:text>
                </div>
            @endif

            @if ($this->children->count() > 0)
                <div>
                    <flux:heading size="sm" class="mb-3">Sub-Items</flux:heading>
                    <div class="space-y-2">
                        @foreach ($this->children as $child)
                            <a 
                                href="{{ route('cleaning-items.show', $child->id) }}"
                                wire:navigate
                                class="block p-4 rounded-lg border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition"
                            >
                                <div class="flex items-center justify-between">
                                    <div>
                                        <flux:text class="font-medium">{{ $child->name }}</flux:text>
                                        @if ($child->cleaning_frequency_hours)
                                            <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">
                                                Dirtiness: {{ round($child->dirtiness_percentage) }}%
                                            </flux:text>
                                        @endif
                                    </div>
                                    @if ($child->cleaning_frequency_hours)
                                        <flux:badge 
                                            variant="{{ $child->is_overdue ? 'danger' : ($child->needs_attention ? 'warning' : 'success') }}"
                                        >
                                            {{ $child->coins_available }} coins
                                        </flux:badge>
                                    @endif
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="flex gap-3">
                @if ($item->cleaning_frequency_hours)
                    <livewire:cleaning-items.clean-button :item-id="$item->id" />
                @endif
                <flux:button
                    variant="primary"
                    wire:navigate
                    href="{{ route('cleaning-items.create', ['groupId' => $item->group_id, 'parentId' => $item->id]) }}"
                >
                    Add Sub-Item
                </flux:button>
                <flux:button
                    variant="ghost"
                    wire:navigate
                    href="{{ route('groups.show', $item->group_id) }}"
                >
                    Back to Group
                </flux:button>
            </div>
        </div>
    </div>

    <livewire:cleaning-items.clean-modal />
</div>
