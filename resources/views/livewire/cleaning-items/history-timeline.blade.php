<?php

use Livewire\Volt\Component;

use function Livewire\Volt\{state};

state(['cleaningHistory' => []]);

new class extends Component {
    //
}; ?>

<div>
    @if (count($cleaningHistory) > 0)
        <div class="p-6 bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700">
            <flux:heading size="sm" class="mb-4">Cleaning History</flux:heading>
            
            <div class="space-y-4">
                @foreach ($cleaningHistory as $log)
                    <div class="flex gap-4">
                        {{-- Timeline indicator --}}
                        <div class="flex flex-col items-center">
                            <div class="w-3 h-3 rounded-full bg-green-500"></div>
                            @if (!$loop->last)
                                <div class="w-0.5 h-full bg-zinc-200 dark:bg-zinc-700 mt-1"></div>
                            @endif
                        </div>
                        
                        {{-- Log details --}}
                        <div class="flex-1 pb-4">
                            <div class="flex items-start justify-between">
                                <div>
                                    <flux:text class="font-medium">
                                        {{ $log->user->name }}
                                    </flux:text>
                                    <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">
                                        {{ $log->cleaned_at->diffForHumans() }}
                                        <span class="mx-1">•</span>
                                        {{ $log->cleaned_at->format('M j, Y g:i A') }}
                                    </flux:text>
                                </div>
                                <flux:badge variant="success">
                                    +{{ $log->coins_earned }} coins
                                </flux:badge>
                            </div>
                            
                            <div class="mt-2 flex items-center gap-4">
                                <flux:text size="sm" class="text-zinc-600 dark:text-zinc-300">
                                    Dirtiness: {{ round($log->dirtiness_at_clean) }}%
                                </flux:text>
                            </div>
                            
                            @if ($log->notes)
                                <div class="mt-2 p-3 bg-zinc-50 dark:bg-zinc-900 rounded-lg">
                                    <flux:text size="sm" class="text-zinc-600 dark:text-zinc-300">
                                        {{ $log->notes }}
                                    </flux:text>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
