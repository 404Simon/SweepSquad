<?php

use Livewire\Volt\Component;

new class extends Component {
    public int $itemId;

    public function openModal(): void
    {
        $this->dispatch('open-clean-modal', itemId: $this->itemId);
    }
}; ?>

<div>
    <flux:button 
        variant="primary" 
        wire:click="openModal"
        class="touch-target transition-smooth"
    >
        Mark as Cleaned
    </flux:button>
</div>
