@props(['statistics' => []])

<div>
    @if ($statistics['total_cleanings'] > 0)
        <div class="p-6 bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700">
            <flux:heading size="sm" class="mb-4">Statistics</flux:heading>
            
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                {{-- Total Cleanings --}}
                <div>
                    <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400 mb-1">Total Cleanings</flux:text>
                    <flux:text class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">
                        {{ $statistics['total_cleanings'] }}
                    </flux:text>
                </div>

                {{-- Average Dirtiness --}}
                <div>
                    <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400 mb-1">Avg. Dirtiness</flux:text>
                    <flux:text class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">
                        {{ $statistics['average_dirtiness'] }}%
                    </flux:text>
                </div>

                {{-- Most Frequent Cleaner --}}
                <div>
                    <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400 mb-1">Top Cleaner</flux:text>
                    <flux:text class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">
                        @if ($statistics['most_frequent_cleaner'])
                            {{ $statistics['most_frequent_cleaner']->name }}
                        @else
                            -
                        @endif
                    </flux:text>
                </div>

                {{-- Average Time Between Cleanings --}}
                <div>
                    <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400 mb-1">Avg. Time Between</flux:text>
                    <flux:text class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">
                        @if ($statistics['average_hours_between'])
                            {{ $statistics['average_hours_between'] }}h
                        @else
                            -
                        @endif
                    </flux:text>
                </div>
            </div>
        </div>
    @endif
</div>
