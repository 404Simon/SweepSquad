<?php

use App\Models\Group;
use Livewire\Volt\Component;

new class extends Component {
    public Group $group;
}; ?>

<a
    href="{{ route('groups.show', $group) }}"
    wire:navigate
    class="block p-6 bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 hover:border-zinc-300 dark:hover:border-zinc-600 transition-colors"
>
    <flux:heading size="sm" class="mb-3">{{ $group->name }}</flux:heading>
    
    @if($group->description)
        <flux:text class="text-sm text-zinc-600 dark:text-zinc-400 mb-4">
            {{ Str::limit($group->description, 80) }}
        </flux:text>
    @endif
    
    <div class="flex gap-4 text-sm">
        @if($group->items_needing_attention > 0)
            <div class="flex items-center gap-1">
                <span class="text-orange-500">⚠️</span>
                <flux:text class="text-zinc-600 dark:text-zinc-400">
                    {{ $group->items_needing_attention }} need{{ $group->items_needing_attention === 1 ? 's' : '' }} attention
                </flux:text>
            </div>
        @endif
        
        @if($group->items_overdue > 0)
            <div class="flex items-center gap-1">
                <span class="text-red-500">🔴</span>
                <flux:text class="text-zinc-600 dark:text-zinc-400">
                    {{ $group->items_overdue }} overdue
                </flux:text>
            </div>
        @endif
        
        @if($group->items_needing_attention === 0 && $group->items_overdue === 0)
            <div class="flex items-center gap-1">
                <span class="text-green-500">✅</span>
                <flux:text class="text-zinc-600 dark:text-zinc-400">
                    All caught up!
                </flux:text>
            </div>
        @endif
    </div>
</a>
