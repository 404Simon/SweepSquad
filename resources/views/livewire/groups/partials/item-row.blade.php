@php
    $dirtiness = $item->dirtiness_percentage;
    $colorClass = match(true) {
        $dirtiness >= 100 => 'bg-red-600 dark:bg-red-500',
        $dirtiness >= 80 => 'bg-red-500 dark:bg-red-400',
        $dirtiness >= 50 => 'bg-orange-500 dark:bg-orange-400',
        $dirtiness >= 20 => 'bg-yellow-500 dark:bg-yellow-400',
        default => 'bg-green-500 dark:bg-green-400',
    };
    
    $statusText = match(true) {
        $dirtiness >= 100 => 'Overdue',
        $dirtiness >= 80 => 'Needs Attention',
        $dirtiness >= 20 => 'Getting Dirty',
        default => 'Clean',
    };
    
    $statusTextColor = match(true) {
        $dirtiness >= 100 => 'text-red-600 dark:text-red-400',
        $dirtiness >= 80 => 'text-orange-600 dark:text-orange-400',
        $dirtiness >= 20 => 'text-yellow-600 dark:text-yellow-400',
        default => 'text-green-600 dark:text-green-400',
    };
    
    $paddingClass = match($level) {
        0 => 'p-4',
        1 => 'p-4 pl-12',
        2 => 'p-4 pl-20',
        default => 'p-4 pl-28',
    };
@endphp

<div class="hover:bg-zinc-50 dark:hover:bg-zinc-700/50 transition-colors">
    <div class="{{ $paddingClass }}">
        <div class="flex flex-col lg:flex-row lg:items-center gap-4">
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-3 mb-2">
                    @if($level > 0)
                        <span class="text-zinc-400 dark:text-zinc-500">└─</span>
                    @endif
                    <a 
                        href="{{ route('cleaning-items.show', $item) }}" 
                        wire:navigate
                        class="font-medium text-zinc-900 dark:text-zinc-100 hover:text-blue-600 dark:hover:text-blue-400"
                    >
                        {{ $item->name }}
                    </a>
                    <flux:badge variant="{{ $dirtiness >= 80 ? 'danger' : ($dirtiness >= 20 ? 'warning' : 'success') }}" class="text-xs">
                        {{ $statusText }}
                    </flux:badge>
                </div>
                
                @if($item->description)
                    <flux:text class="text-sm text-zinc-600 dark:text-zinc-400 mb-2">
                        {{ Str::limit($item->description, 100) }}
                    </flux:text>
                @endif
                
                <div class="flex items-center gap-4 text-sm text-zinc-500">
                    @if($item->last_cleaned_at)
                        <span>
                            Last cleaned: {{ $item->last_cleaned_at->diffForHumans() }}
                            @if($item->lastCleanedByUser)
                                by {{ $item->lastCleanedByUser->name }}
                            @endif
                        </span>
                    @else
                        <span>Never cleaned</span>
                    @endif
                    
                    @if($item->next_cleaning_at)
                        <span>
                            Due: {{ $item->next_cleaning_at->diffForHumans() }}
                        </span>
                    @endif
                </div>
            </div>
            
            <div class="flex flex-col lg:flex-row items-stretch lg:items-center gap-4 lg:w-1/3">
                <div class="flex-1">
                    <div class="mb-2">
                        <div class="flex justify-between items-center mb-1">
                            <flux:text class="text-xs font-medium {{ $statusTextColor }}">
                                {{ number_format($dirtiness, 0) }}%
                            </flux:text>
                            <flux:text class="text-xs font-medium text-zinc-600 dark:text-zinc-400">
                                {{ $item->coins_available }} coins
                            </flux:text>
                        </div>
                        <div class="w-full bg-zinc-200 dark:bg-zinc-700 rounded-full h-2.5">
                            <div class="{{ $colorClass }} h-2.5 rounded-full transition-all duration-500 w-0" 
                                 x-data 
                                 x-init="$nextTick(() => $el.style.width = '{{ min($dirtiness, 100) }}%')">
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="flex gap-2">
                    <flux:button
                        variant="primary"
                        size="sm"
                        wire:click="markAsCleaned({{ $item->id }})"
                        wire:confirm="Mark {{ $item->name }} as cleaned?"
                    >
                        Clean
                    </flux:button>
                </div>
            </div>
        </div>
    </div>
    
    @if($item->children->isNotEmpty())
        @foreach($item->children as $child)
            @include('livewire.groups.partials.item-row', ['item' => $child, 'level' => $level + 1])
        @endforeach
    @endif
</div>
