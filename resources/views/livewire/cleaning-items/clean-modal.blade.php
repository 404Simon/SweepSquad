<?php

use App\Actions\CleaningItems\MarkAsCleanedAction;
use App\Models\CleaningItem;
use App\Models\User;
use Livewire\Volt\Component;

use function Livewire\Volt\{state};

state(['showModal' => false, 'item' => null, 'notes' => '', 'coinsEarned' => 0, 'showSuccess' => false]);

$openModal = function (int $itemId) {
    $this->item = CleaningItem::findOrFail($itemId);
    $this->showModal = true;
    $this->notes = '';
    $this->coinsEarned = 0;
    $this->showSuccess = false;
};

$closeModal = function () {
    $this->showModal = false;
    $this->item = null;
    $this->notes = '';
};

$markAsCleaned = function (MarkAsCleanedAction $action) {
    if (!$this->item) {
        return;
    }

    $log = $action->handle($this->item, User::find(auth()->id()), $this->notes);
    $this->coinsEarned = $log->coins_earned;
    
    $this->showModal = false;
    $this->showSuccess = true;
    
    $this->dispatch('item-cleaned');
};

$closeSuccess = function () {
    $this->showSuccess = false;
    $this->item = null;
    $this->coinsEarned = 0;
};

new class extends Component {
    //
}; ?>

<div>
    @if ($showModal && $item)
        <flux:modal name="clean-modal" variant="flyout" wire:model="showModal">
            <form wire:submit="markAsCleaned" class="space-y-6">
                <div>
                    <flux:heading size="lg">Mark as Cleaned</flux:heading>
                    <flux:text class="text-zinc-500 dark:text-zinc-400">
                        {{ $item->name }}
                    </flux:text>
                </div>

                <div class="space-y-4">
                    <div>
                        <flux:heading size="sm" class="mb-2">Current Dirtiness</flux:heading>
                        <div class="flex items-center gap-3">
                            <flux:text class="text-2xl font-bold">
                                {{ round($item->dirtiness_percentage) }}%
                            </flux:text>
                            @if ($item->is_overdue)
                                <flux:badge variant="danger">Overdue</flux:badge>
                            @elseif ($item->needs_attention)
                                <flux:badge variant="warning">Needs Attention</flux:badge>
                            @else
                                <flux:badge variant="success">Good</flux:badge>
                            @endif
                        </div>
                    </div>

                    <div>
                        <flux:heading size="sm" class="mb-2">Coins to be Earned</flux:heading>
                        <flux:text class="text-2xl font-bold text-green-600 dark:text-green-400">
                            {{ $item->coins_available }} coins
                        </flux:text>
                    </div>

                    <flux:field>
                        <flux:label>Notes (Optional)</flux:label>
                        <flux:textarea 
                            wire:model="notes" 
                            placeholder="Add any notes about this cleaning..."
                            rows="3"
                        />
                    </flux:field>
                </div>

                <div class="flex gap-3 justify-end">
                    <flux:button variant="ghost" type="button" wire:click="closeModal">
                        Cancel
                    </flux:button>
                    <flux:button variant="primary" type="submit">
                        Confirm Clean
                    </flux:button>
                </div>
            </form>
        </flux:modal>
    @endif

    @if ($showSuccess)
        <flux:modal name="success-modal" variant="flyout" wire:model="showSuccess">
            <div class="space-y-6 text-center">
                <div class="flex justify-center">
                    <div class="w-20 h-20 rounded-full bg-green-100 dark:bg-green-900 flex items-center justify-center">
                        <svg class="w-12 h-12 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                </div>
                
                <div>
                    <flux:heading size="lg" class="mb-2">Great Job!</flux:heading>
                    <flux:text class="text-zinc-500 dark:text-zinc-400">
                        You earned
                    </flux:text>
                    <flux:text class="text-3xl font-bold text-green-600 dark:text-green-400">
                        {{ $coinsEarned }} coins
                    </flux:text>
                </div>

                <flux:button variant="primary" wire:click="closeSuccess" class="w-full">
                    Awesome!
                </flux:button>
            </div>
        </flux:modal>
    @endif
</div>

@script
<script>
    Livewire.on('open-clean-modal', (event) => {
        $wire.call('openModal', event.itemId);
    });
</script>
@endscript
