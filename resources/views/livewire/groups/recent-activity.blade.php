<?php

use App\Models\CleaningLog;
use App\Models\Group;
use Livewire\Volt\Component;

new class extends Component {
    public Group $group;

    public int $limit = 10;

    public function with(): array
    {
        $recentLogs = CleaningLog::query()
            ->where('group_id', $this->group->id)
            ->with(['user', 'cleaningItem'])
            ->latest('cleaned_at')
            ->limit($this->limit)
            ->get();

        return [
            'recentLogs' => $recentLogs,
        ];
    }
}; ?>

<div>
    <div class="space-y-4">
        <div>
            <flux:heading size="lg">Recent Activity</flux:heading>
            <flux:text>See what your group members have been cleaning</flux:text>
        </div>

        @if ($recentLogs->isEmpty())
            <div class="rounded-lg border border-zinc-200 bg-white p-8 text-center dark:border-zinc-700 dark:bg-zinc-800">
                <div class="text-5xl mb-3">🧹</div>
                <flux:heading size="base" class="mb-2">No activity yet</flux:heading>
                <flux:text class="text-zinc-500">Start cleaning to see activity here!</flux:text>
            </div>
        @else
            <div class="space-y-2">
                @foreach ($recentLogs as $log)
                    <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-1">
                                    <flux:text class="font-medium">{{ $log->user->name }}</flux:text>
                                    <flux:text class="text-zinc-500">cleaned</flux:text>
                                    <flux:text class="font-medium">{{ $log->cleaningItem->name }}</flux:text>
                                </div>
                                <flux:text class="text-sm text-zinc-500">
                                    {{ $log->cleaned_at->diffForHumans() }}
                                </flux:text>
                            </div>
                            <div class="flex items-center gap-1 text-sm">
                                <span>💰</span>
                                <flux:text class="font-medium text-green-600 dark:text-green-400">
                                    +{{ $log->coins_earned }}
                                </flux:text>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
