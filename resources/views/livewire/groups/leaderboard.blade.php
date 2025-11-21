<?php

use App\Models\CleaningLog;
use App\Models\Group;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Reactive;
use Livewire\Volt\Component;

new class extends Component {
    #[Reactive]
    public Group $group;

    public string $period = 'week';

    public function setPeriod(string $period): void
    {
        $this->period = $period;
    }

    public function with(): array
    {
        // Get date range based on period
        $startDate = match ($this->period) {
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'all' => null,
        };

        // Top cleaners (by coins earned)
        $topCleaners = CleaningLog::query()
            ->where('group_id', $this->group->id)
            ->when($startDate, fn($query) => $query->where('cleaned_at', '>=', $startDate))
            ->select('user_id', DB::raw('SUM(coins_earned) as total_coins'), DB::raw('COUNT(*) as clean_count'))
            ->groupBy('user_id')
            ->orderByDesc('total_coins')
            ->limit(10)
            ->with('user')
            ->get();

        // Most consistent member (most days with cleanings)
        $mostConsistent = CleaningLog::query()
            ->where('group_id', $this->group->id)
            ->when($startDate, fn($query) => $query->where('cleaned_at', '>=', $startDate))
            ->select('user_id', DB::raw('COUNT(DISTINCT DATE(cleaned_at)) as active_days'))
            ->groupBy('user_id')
            ->orderByDesc('active_days')
            ->with('user')
            ->first();

        return [
            'topCleaners' => $topCleaners,
            'mostConsistent' => $mostConsistent,
            'period' => $this->period,
        ];
    }
}; ?>

<div>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="lg">Group Leaderboard</flux:heading>
                <flux:text>See who's cleaning the most</flux:text>
            </div>

            <div class="flex gap-2">
                <flux:button wire:click="setPeriod('week')" variant="{{ $period === 'week' ? 'primary' : 'ghost' }}" size="sm">
                    This Week
                </flux:button>
                <flux:button wire:click="setPeriod('month')" variant="{{ $period === 'month' ? 'primary' : 'ghost' }}" size="sm">
                    This Month
                </flux:button>
                <flux:button wire:click="setPeriod('all')" variant="{{ $period === 'all' ? 'primary' : 'ghost' }}" size="sm">
                    All Time
                </flux:button>
            </div>
        </div>

        <!-- Top Cleaners -->
        <div>
            <flux:heading size="md" class="mb-4">Top Cleaners</flux:heading>
            @if ($topCleaners->isEmpty())
                <div class="rounded-lg border border-neutral-200 bg-white p-8 text-center dark:border-neutral-700 dark:bg-neutral-800">
                    <flux:text>No cleaning data available for this period</flux:text>
                </div>
            @else
                <div class="space-y-2">
                    @foreach ($topCleaners as $index => $log)
                        <div class="flex items-center gap-4 rounded-lg border border-neutral-200 bg-white p-4 dark:border-neutral-700 dark:bg-neutral-800">
                            <div class="flex size-10 flex-shrink-0 items-center justify-center rounded-full {{ $index === 0 ? 'bg-yellow-500 text-white' : ($index === 1 ? 'bg-gray-400 text-white' : ($index === 2 ? 'bg-amber-700 text-white' : 'bg-neutral-200 text-neutral-700 dark:bg-neutral-700 dark:text-neutral-300')) }}">
                                <flux:heading size="sm">{{ $index + 1 }}</flux:heading>
                            </div>
                            <flux:avatar :src="null" :alt="$log->user->name" />
                            <div class="flex-1">
                                <flux:heading size="sm">{{ $log->user->name }}</flux:heading>
                                <flux:text class="text-sm">{{ $log->clean_count }} cleans</flux:text>
                            </div>
                            <div class="text-right">
                                <div class="flex items-center gap-1">
                                    <flux:icon.currency-dollar class="size-5 text-yellow-500" />
                                    <flux:heading size="sm">{{ number_format($log->total_coins) }}</flux:heading>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Most Consistent Member -->
        @if ($mostConsistent)
            <div>
                <flux:heading size="md" class="mb-4">Most Consistent Member</flux:heading>
                <div class="rounded-lg border border-green-500 bg-green-50 p-6 dark:border-green-700 dark:bg-green-900/20">
                    <div class="flex items-center gap-4">
                        <flux:avatar :src="null" :alt="$mostConsistent->user->name" class="size-12" />
                        <div class="flex-1">
                            <flux:heading size="md">{{ $mostConsistent->user->name }}</flux:heading>
                            <flux:text>Active {{ $mostConsistent->active_days }} {{ Str::plural('day', $mostConsistent->active_days) }} {{ $period === 'week' ? 'this week' : ($period === 'month' ? 'this month' : 'total') }}</flux:text>
                        </div>
                        <flux:icon.fire class="size-12 text-orange-500" />
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
