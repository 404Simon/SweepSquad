<?php

use App\Models\CleaningLog;
use Livewire\Volt\Component;

new class extends Component {
    public CleaningLog $log;
}; ?>

<div class="p-4 hover:bg-zinc-50 dark:hover:bg-zinc-700/50 transition-colors">
    <div class="flex items-start justify-between gap-4">
        <div class="flex-1">
            <div class="flex items-center gap-2 mb-1">
                <flux:text class="font-medium">{{ $log->user->name }}</flux:text>
                <flux:text class="text-zinc-500">cleaned</flux:text>
                <flux:text class="font-medium">{{ $log->cleaningItem->name }}</flux:text>
            </div>
            <div class="flex items-center gap-3 text-sm">
                <flux:text class="text-zinc-500">
                    in {{ $log->group->name }}
                </flux:text>
                <flux:text class="text-zinc-400">•</flux:text>
                <flux:text class="text-zinc-500">
                    {{ $log->cleaned_at->diffForHumans() }}
                </flux:text>
            </div>
        </div>
        <div class="flex items-center gap-1 text-sm">
            <span>💰</span>
            <flux:text class="font-medium text-green-600 dark:text-green-400">
                +{{ $log->coins_earned }}
            </flux:text>
        </div>
    </div>
</div>
