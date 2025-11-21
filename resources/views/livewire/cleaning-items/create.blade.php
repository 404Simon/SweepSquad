<?php

use App\Actions\CleaningItems\CreateCleaningItemAction;
use App\Models\CleaningItem;
use App\Models\Group;
use Livewire\Volt\Component;

use function Livewire\Volt\layout;

layout('components.layouts.app');

new class extends Component
{
    public int $groupId;

    public ?int $parentId = null;

    public string $name = '';

    public string $description = '';

    public ?int $cleaningFrequencyHours = null;

    public int $baseCoinReward = 0;

    public Group $group;

    public ?CleaningItem $parent = null;

    public function mount(int $groupId, ?int $parentId = null): void
    {
        $this->groupId = $groupId;
        $this->parentId = $parentId;
        $this->group = Group::query()->findOrFail($groupId);

        if ($parentId !== null) {
            $this->parent = CleaningItem::query()->findOrFail($parentId);
        }
    }

    /**
     * Create a new cleaning item.
     */
    public function create(CreateCleaningItemAction $action): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'cleaningFrequencyHours' => ['nullable', 'integer', 'min:1'],
            'baseCoinReward' => ['required', 'integer', 'min:0'],
        ]);

        $action->handle(
            $this->group,
            $validated['name'],
            $validated['description'] ?? null,
            $validated['cleaningFrequencyHours'],
            $validated['baseCoinReward'],
            $this->parentId,
        );

        session()->flash('success', 'Cleaning item created successfully!');

        $this->redirect(route('groups.show', $this->groupId), navigate: true);
    }
}; ?>

<div>
    <div class="max-w-2xl mx-auto px-4 py-8">
        <flux:heading size="lg" class="mb-6">
            Create Cleaning Item
            @if ($parent)
                <span class="text-sm text-zinc-500 dark:text-zinc-400">under {{ $parent->name }}</span>
            @endif
        </flux:heading>

        <form wire:submit="create" class="space-y-6">
            <flux:field>
                <flux:label for="name">Name</flux:label>
                <flux:input
                    id="name"
                    wire:model="name"
                    type="text"
                    placeholder="e.g. Living Room, Kitchen Floor, etc."
                    required
                />
                @error('name')
                    <flux:error>{{ $message }}</flux:error>
                @enderror
            </flux:field>

            <flux:field>
                <flux:label for="description">Description</flux:label>
                <flux:textarea
                    id="description"
                    wire:model="description"
                    placeholder="Optional description"
                    rows="3"
                />
                @error('description')
                    <flux:error>{{ $message }}</flux:error>
                @enderror
            </flux:field>

            <flux:field>
                <flux:label for="cleaningFrequencyHours">Cleaning Frequency (hours)</flux:label>
                <flux:input
                    id="cleaningFrequencyHours"
                    wire:model="cleaningFrequencyHours"
                    type="number"
                    placeholder="e.g. 24, 168, etc."
                    min="1"
                />
                <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">
                    How often should this be cleaned? Leave empty if no schedule is needed.
                </flux:text>
                @error('cleaningFrequencyHours')
                    <flux:error>{{ $message }}</flux:error>
                @enderror
            </flux:field>

            <flux:field>
                <flux:label for="baseCoinReward">Base Coin Reward</flux:label>
                <flux:input
                    id="baseCoinReward"
                    wire:model="baseCoinReward"
                    type="number"
                    placeholder="0"
                    min="0"
                    required
                />
                <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">
                    Coins awarded for completing this task (multipliers apply based on dirtiness).
                </flux:text>
                @error('baseCoinReward')
                    <flux:error>{{ $message }}</flux:error>
                @enderror
            </flux:field>

            <div class="flex gap-3">
                <flux:button type="submit" variant="primary">
                    Create Item
                </flux:button>
                <flux:button
                    type="button"
                    variant="ghost"
                    wire:navigate
                    href="{{ route('groups.show', $groupId) }}"
                >
                    Cancel
                </flux:button>
            </div>
        </form>
    </div>
</div>
