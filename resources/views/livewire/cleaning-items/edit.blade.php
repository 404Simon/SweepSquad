<?php

use App\Actions\CleaningItems\DeleteCleaningItemAction;
use App\Actions\CleaningItems\UpdateCleaningItemAction;
use App\Models\CleaningItem;
use Livewire\Attributes\Locked;
use Livewire\Volt\Component;

use function Livewire\Volt\layout;

layout('components.layouts.app');

new class extends Component
{
    #[Locked]
    public CleaningItem $item;

    public string $name = '';

    public ?string $description = '';

    public ?int $cleaningFrequencyHours = null;

    public int $baseCoinReward = 0;

    /**
     * Mount the component.
     */
    public function mount(int $id): void
    {
        $this->item = CleaningItem::query()->findOrFail($id);
        $this->name = $this->item->name;
        $this->description = $this->item->description ?? '';
        $this->cleaningFrequencyHours = $this->item->cleaning_frequency_hours;
        $this->baseCoinReward = $this->item->base_coin_reward;
    }

    /**
     * Update the cleaning item.
     */
    public function update(UpdateCleaningItemAction $action): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'cleaningFrequencyHours' => ['nullable', 'integer', 'min:1'],
            'baseCoinReward' => ['required', 'integer', 'min:0'],
        ]);

        $action->handle(
            $this->item,
            $validated['name'],
            $validated['description'] ?? null,
            $validated['cleaningFrequencyHours'],
            $validated['baseCoinReward'],
        );

        session()->flash('success', 'Cleaning item updated successfully!');

        $this->redirect(route('groups.show', $this->item->group_id), navigate: true);
    }

    /**
     * Delete the cleaning item.
     */
    public function delete(DeleteCleaningItemAction $action): void
    {
        $groupId = $this->item->group_id;

        $action->handle($this->item);

        session()->flash('success', 'Cleaning item deleted successfully!');

        $this->redirect(route('groups.show', $groupId), navigate: true);
    }
}; ?>

<div>
    <div class="max-w-2xl mx-auto px-4 py-8">
        <flux:heading size="lg" class="mb-6">Edit Cleaning Item</flux:heading>

        <form wire:submit="update" class="space-y-6">
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

            <div class="flex justify-between gap-3">
                <div class="flex gap-3">
                    <flux:button type="submit" variant="primary">
                        Update Item
                    </flux:button>
                    <flux:button
                        type="button"
                        variant="ghost"
                        wire:navigate
                        href="{{ route('groups.show', $item->group_id) }}"
                    >
                        Cancel
                    </flux:button>
                </div>
                
                <flux:button
                    type="button"
                    variant="danger"
                    wire:click="delete"
                    wire:confirm="Are you sure you want to delete this item? All sub-items will also be deleted."
                >
                    Delete
                </flux:button>
            </div>
        </form>
    </div>
</div>
