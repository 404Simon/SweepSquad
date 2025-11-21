<?php

use App\GroupRole;
use App\Models\CleaningItem;
use App\Models\CleaningLog;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Volt\Component;

use function Livewire\Volt\{layout};

layout('components.layouts.app');

new class extends Component {
    #[Locked]
    public int $itemId;

    public CleaningItem $item;

    public function mount(int $id): void
    {
        $this->itemId = $id;

        // Load the item with all necessary relationships
        $this->item = CleaningItem::query()
            ->with(['children', 'lastCleanedByUser', 'parent', 'group.members'])
            ->findOrFail($id);

        // Check if user is a member of the group
        if (! $this->item->group->members->contains(Auth::id())) {
            abort(403, 'You are not a member of this group.');
        }
    }

    #[Computed]
    public function children()
    {
        return $this->item->children()->orderBy('order')->get();
    }

    #[Computed]
    public function cleaningHistory()
    {
        return CleaningLog::query()
            ->where('cleaning_item_id', $this->itemId)
            ->with('user')
            ->orderBy('cleaned_at', 'desc')
            ->limit(10)
            ->get();
    }

    #[Computed]
    public function statistics(): array
    {
        $logs = CleaningLog::query()
            ->where('cleaning_item_id', $this->itemId)
            ->get();

        if ($logs->isEmpty()) {
            return [
                'total_cleanings' => 0,
                'average_dirtiness' => 0,
                'most_frequent_cleaner' => null,
                'average_hours_between' => null,
            ];
        }

        // Total cleanings
        $totalCleanings = $logs->count();

        // Average dirtiness
        $averageDirtiness = round($logs->avg('dirtiness_at_clean'), 1);

        // Most frequent cleaner
        $cleanerCounts = $logs->groupBy('user_id')->map->count()->sortDesc();
        $mostFrequentCleanerId = $cleanerCounts->keys()->first();
        $mostFrequentCleaner = $mostFrequentCleanerId
            ? $logs->firstWhere('user_id', $mostFrequentCleanerId)?->user
            : null;

        // Average time between cleanings
        $sortedLogs = $logs->sortBy('cleaned_at')->values();
        $timeDifferences = [];

        for ($i = 1; $i < $sortedLogs->count(); $i++) {
            $diff = $sortedLogs[$i]->cleaned_at->diffInHours($sortedLogs[$i - 1]->cleaned_at);
            $timeDifferences[] = $diff;
        }

        $averageHoursBetween = ! empty($timeDifferences)
            ? round(array_sum($timeDifferences) / count($timeDifferences), 1)
            : null;

        return [
            'total_cleanings' => $totalCleanings,
            'average_dirtiness' => $averageDirtiness,
            'most_frequent_cleaner' => $mostFrequentCleaner,
            'average_hours_between' => $averageHoursBetween,
        ];
    }

    #[Computed]
    public function isAdmin(): bool
    {
        $member = $this->item->group->members->firstWhere('id', Auth::id());

        return $member && in_array($member->pivot->role, [GroupRole::Owner, GroupRole::Admin]);
    }

    #[Computed]
    public function hoursUntilFullyDirty(): ?float
    {
        if (! $this->item->cleaning_frequency_hours || ! $this->item->last_cleaned_at) {
            return null;
        }

        $dirtiness = $this->item->calculateDirtiness();
        if ($dirtiness >= 100) {
            return 0;
        }

        $hoursSinceLastClean = $this->item->last_cleaned_at->diffInHours(now());
        $hoursToFull = $this->item->cleaning_frequency_hours - $hoursSinceLastClean;

        return max(0, round($hoursToFull, 1));
    }

    public function refreshItem(): void
    {
        $this->item = CleaningItem::query()
            ->with(['children', 'lastCleanedByUser', 'parent', 'group.members'])
            ->findOrFail($this->itemId);

        // Clear computed property cache
        unset($this->children, $this->cleaningHistory, $this->statistics, $this->isAdmin, $this->hoursUntilFullyDirty);
    }
}; ?>

<div x-on:item-cleaned.window="$wire.call('refreshItem')">
    <div class="max-w-4xl mx-auto px-4 py-8">
        {{-- Breadcrumb Navigation --}}
        <nav aria-label="Breadcrumb" class="mb-6">
            <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">
                <a href="{{ route('groups.show', $item->group_id) }}" wire:navigate class="hover:text-zinc-700 dark:hover:text-zinc-300 focus-ring rounded">
                    {{ $item->group->name }}
                </a>
                @if ($item->parent)
                    <span class="mx-2" aria-hidden="true">/</span>
                    <a href="{{ route('cleaning-items.show', $item->parent->id) }}" wire:navigate class="hover:text-zinc-700 dark:hover:text-zinc-300 focus-ring rounded">
                        {{ $item->parent->name }}
                    </a>
                @endif
                <span class="mx-2" aria-hidden="true">/</span>
                <span class="text-zinc-900 dark:text-zinc-100" aria-current="page">{{ $item->name }}</span>
            </flux:text>
        </nav>

        {{-- Header --}}
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex-1">
                <flux:heading size="lg">{{ $item->name }}</flux:heading>
                @if ($item->description)
                    <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400 mt-1">
                        {{ $item->description }}
                    </flux:text>
                @endif
            </div>
            @if ($this->isAdmin)
                <flux:button
                    variant="ghost"
                    wire:navigate
                    href="{{ route('cleaning-items.edit', $item->id) }}"
                    class="touch-target w-full sm:w-auto"
                >
                    Edit
                </flux:button>
            @endif
        </div>

        <div class="space-y-6">
            @if ($item->cleaning_frequency_hours)
                {{-- Status Card with Circular Progress --}}
                <div class="p-6 bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700">
                    <div class="flex flex-col md:flex-row items-center gap-8">
                        {{-- Circular Progress Indicator --}}
                        <x-cleaning-items.circular-progress :dirtiness="$item->dirtiness_percentage" />

                        {{-- Status Info --}}
                        <div class="flex-1 space-y-4 text-center md:text-left">
                            <div>
                                <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">Time Until 100% Dirty</flux:text>
                                <flux:text class="text-2xl font-bold">
                                    @if ($this->hoursUntilFullyDirty === 0)
                                        <span class="text-red-600 dark:text-red-400">Overdue!</span>
                                    @elseif ($this->hoursUntilFullyDirty)
                                        {{ $this->hoursUntilFullyDirty }} hours
                                    @else
                                        Never cleaned
                                    @endif
                                </flux:text>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">Base Reward</flux:text>
                                    <flux:text class="text-lg font-semibold">{{ $item->base_coin_reward }} coins</flux:text>
                                </div>
                                <div>
                                    <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">Available Now</flux:text>
                                    <flux:text class="text-lg font-semibold text-green-600 dark:text-green-400">
                                        {{ $item->coins_available }} coins
                                        @if ($item->coins_available > $item->base_coin_reward)
                                            <flux:badge variant="warning" class="ml-1">Bonus!</flux:badge>
                                        @endif
                                    </flux:text>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="mt-6 flex flex-col sm:flex-row gap-3">
                        <div class="flex-1 sm:flex-none">
                            <livewire:cleaning-items.clean-button :item-id="$item->id" />
                        </div>
                        @if ($this->isAdmin)
                            <flux:button
                                variant="ghost"
                                wire:navigate
                                href="{{ route('cleaning-items.edit', $item->id) }}"
                                class="touch-target"
                            >
                                Edit Item
                            </flux:button>
                        @endif
                    </div>
                </div>

                {{-- Statistics Card --}}
                <x-cleaning-items.stats-grid :statistics="$this->statistics" />

                {{-- Cleaning History Timeline --}}
                <x-cleaning-items.history-timeline :cleaning-history="$this->cleaningHistory" />
            @else
                {{-- Container/Room Item (no cleaning frequency) --}}
                <div class="p-6 bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700">
                    <flux:text class="text-zinc-500 dark:text-zinc-400">
                        This is a container item. Add sub-items to track individual cleaning tasks.
                    </flux:text>
                </div>
            @endif

            {{-- Sub-Items --}}
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

            {{-- Navigation Buttons --}}
            <div class="flex flex-col sm:flex-row gap-3">
                <flux:button
                    variant="primary"
                    wire:navigate
                    href="{{ route('cleaning-items.create', ['groupId' => $item->group_id, 'parentId' => $item->id]) }}"
                    class="touch-target"
                >
                    Add Sub-Item
                </flux:button>
                <flux:button
                    variant="ghost"
                    wire:navigate
                    href="{{ route('groups.show', $item->group_id) }}"
                    class="touch-target"
                >
                    Back to Group
                </flux:button>
            </div>
        </div>
    </div>

    <livewire:cleaning-items.clean-modal />
</div>
