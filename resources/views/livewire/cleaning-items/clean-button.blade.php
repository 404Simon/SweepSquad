<?php

use App\Models\CleaningItem;
use Livewire\Attributes\Modelable;
use Livewire\Volt\Component;

use function Livewire\Volt\{state};

#[Modelable]
state('itemId');

$openModal = function () {
    $this->dispatch('open-clean-modal', itemId: $this->itemId);
};

new class extends Component {
    //
}; ?>

<div>
    <flux:button 
        variant="primary" 
        wire:click="openModal"
    >
        Mark as Cleaned
    </flux:button>
</div>
