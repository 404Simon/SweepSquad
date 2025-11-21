<?php

use Livewire\Volt\Component;

new class extends Component {
    public string $title;
    public int $value;
    public string $icon;
}; ?>

<div class="p-6 bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700">
    <div class="flex items-center justify-between mb-2">
        <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">{{ $title }}</flux:text>
        <span class="text-2xl">{{ $icon }}</span>
    </div>
    <flux:heading size="lg">{{ number_format($value) }}</flux:heading>
</div>
