<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

use function Livewire\Volt\{layout};

layout('components.layouts.app');

new class extends Component {
    public function with(): array
    {
        /** @var User $user */
        $user = Auth::user();

        // Get today's stats
        $todayStart = now()->startOfDay();
        $todayLogs = $user->cleaningLogs()
            ->where('cleaned_at', '>=', $todayStart)
            ->get();

        $todayCoins = $todayLogs->sum('coins_earned');
        $todayItemsCleaned = $todayLogs->count();

        // Get user's groups with counts
        $groups = $user->groups()
            ->with(['cleaningItems'])
            ->get()
            ->map(function ($group) {
                $items = $group->cleaningItems;
                
                // Items needing attention (dirtiness >= 80%)
                $needingAttention = $items->filter(function ($item) {
                    return $item->dirtiness_percentage >= 80.0;
                })->count();

                // Items overdue (dirtiness >= 100%)
                $overdue = $items->filter(function ($item) {
                    return $item->dirtiness_percentage >= 100.0;
                })->count();

                $group->items_needing_attention = $needingAttention;
                $group->items_overdue = $overdue;
                
                return $group;
            });

        // Get recent activity across all user's groups
        $recentActivity = $user->cleaningLogs()
            ->with(['user', 'cleaningItem', 'group'])
            ->orderBy('cleaned_at', 'desc')
            ->limit(10)
            ->get();

        return [
            'user' => $user,
            'currentStreak' => $user->current_streak ?? 0,
            'todayCoins' => $todayCoins,
            'todayItemsCleaned' => $todayItemsCleaned,
            'groups' => $groups,
            'recentActivity' => $recentActivity,
        ];
    }
}; ?>

<div>
    <div class="max-w-7xl mx-auto px-4 py-8">
        <!-- Welcome Section -->
        <div class="mb-8">
            <flux:heading size="lg" class="mb-2">Welcome back, {{ $user->name }}!</flux:heading>
            @if($currentStreak > 0)
                <flux:text class="text-zinc-600 dark:text-zinc-400">
                    🔥 Current streak: {{ $currentStreak }} {{ Str::plural('day', $currentStreak) }}
                </flux:text>
            @endif
        </div>

        <!-- Today's Stats Cards -->
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3 mb-8">
            <livewire:dashboard.stats-card 
                title="Coins Earned Today" 
                :value="$todayCoins" 
                icon="💰"
                :key="'coins-' . now()->timestamp"
            />
            <livewire:dashboard.stats-card 
                title="Items Cleaned Today" 
                :value="$todayItemsCleaned" 
                icon="✨"
                :key="'items-' . now()->timestamp"
            />
            <livewire:dashboard.stats-card 
                title="Total Coins" 
                :value="$user->total_coins" 
                icon="🏆"
                :key="'total-' . now()->timestamp"
            />
        </div>

        <!-- Quick Actions -->
        <div class="flex gap-3 mb-8">
            <flux:button
                variant="primary"
                wire:navigate
                href="{{ route('groups.create') }}"
            >
                Create Group
            </flux:button>
            <flux:button
                variant="outline"
                wire:navigate
                href="{{ route('groups.index') }}"
            >
                View All Groups
            </flux:button>
        </div>

        <!-- My Groups Section -->
        @if($groups->isNotEmpty())
            <div class="mb-8">
                <flux:heading size="base" class="mb-4">My Groups</flux:heading>
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    @foreach($groups as $group)
                        <livewire:dashboard.group-card 
                            :group="$group"
                            :key="'group-' . $group->id"
                        />
                    @endforeach
                </div>
            </div>
        @else
            <div class="text-center py-12 mb-8 bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700">
                <flux:text class="text-zinc-500 mb-4">You're not part of any groups yet.</flux:text>
                <flux:button
                    variant="primary"
                    wire:navigate
                    href="{{ route('groups.create') }}"
                >
                    Create Your First Group
                </flux:button>
            </div>
        @endif

        <!-- Recent Activity Feed -->
        @if($recentActivity->isNotEmpty())
            <div>
                <flux:heading size="base" class="mb-4">Recent Activity</flux:heading>
                <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 divide-y divide-zinc-200 dark:divide-zinc-700">
                    @foreach($recentActivity as $log)
                        <livewire:dashboard.activity-item 
                            :log="$log"
                            :key="'activity-' . $log->id"
                        />
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
