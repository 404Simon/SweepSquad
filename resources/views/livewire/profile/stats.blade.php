<?php

use App\Models\CleaningLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Volt\Component;

new class extends Component {
    public function with(): array
    {
        /** @var User $user */
        $user = Auth::user();

        // Total coins (all-time)
        $totalCoins = $user->total_coins;

        // Coins this week
        $coinsThisWeek = CleaningLog::where('user_id', $user->id)
            ->whereBetween('cleaned_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->sum('coins_earned');

        // Coins this month
        $coinsThisMonth = CleaningLog::where('user_id', $user->id)
            ->whereYear('cleaned_at', now()->year)
            ->whereMonth('cleaned_at', now()->month)
            ->sum('coins_earned');

        // Current streak
        $currentStreak = $user->current_streak;

        // Total items cleaned
        $totalItemsCleaned = CleaningLog::where('user_id', $user->id)->count();

        // Favorite room (most cleaned parent item)
        $favoriteRoom = DB::table('cleaning_logs')
            ->join('cleaning_items', 'cleaning_logs.cleaning_item_id', '=', 'cleaning_items.id')
            ->join('cleaning_items as parent_items', 'cleaning_items.parent_id', '=', 'parent_items.id')
            ->where('cleaning_logs.user_id', $user->id)
            ->whereNotNull('cleaning_items.parent_id')
            ->select('parent_items.name', DB::raw('COUNT(*) as clean_count'))
            ->groupBy('parent_items.id', 'parent_items.name')
            ->orderByDesc('clean_count')
            ->first();

        // Achievements earned
        $achievementsEarned = $user->achievements()->count();
        $totalAchievements = count(\App\Achievement::cases());

        return [
            'totalCoins' => $totalCoins,
            'coinsThisWeek' => $coinsThisWeek,
            'coinsThisMonth' => $coinsThisMonth,
            'currentStreak' => $currentStreak,
            'totalItemsCleaned' => $totalItemsCleaned,
            'favoriteRoom' => $favoriteRoom?->name ?? 'None',
            'achievementsEarned' => $achievementsEarned,
            'totalAchievements' => $totalAchievements,
        ];
    }
}; ?>

<div>
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">Your Statistics</flux:heading>
            <flux:text>Track your cleaning progress and achievements</flux:text>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <!-- Total Coins -->
            <div class="rounded-lg border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-800">
                <div class="flex items-center gap-3">
                    <flux:icon.currency-dollar class="size-8 text-yellow-500" />
                    <div>
                        <flux:text class="text-sm text-neutral-500 dark:text-neutral-400">Total Coins</flux:text>
                        <flux:heading size="xl">{{ number_format($totalCoins) }}</flux:heading>
                    </div>
                </div>
            </div>

            <!-- Current Streak -->
            <div class="rounded-lg border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-800">
                <div class="flex items-center gap-3">
                    <flux:icon.fire class="size-8 text-orange-500" />
                    <div>
                        <flux:text class="text-sm text-neutral-500 dark:text-neutral-400">Current Streak</flux:text>
                        <flux:heading size="xl">{{ $currentStreak }} days</flux:heading>
                    </div>
                </div>
            </div>

            <!-- Total Items Cleaned -->
            <div class="rounded-lg border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-800">
                <div class="flex items-center gap-3">
                    <flux:icon.sparkles class="size-8 text-blue-500" />
                    <div>
                        <flux:text class="text-sm text-neutral-500 dark:text-neutral-400">Items Cleaned</flux:text>
                        <flux:heading size="xl">{{ number_format($totalItemsCleaned) }}</flux:heading>
                    </div>
                </div>
            </div>

            <!-- Achievements -->
            <div class="rounded-lg border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-800">
                <div class="flex items-center gap-3">
                    <flux:icon.star class="size-8 text-purple-500" />
                    <div>
                        <flux:text class="text-sm text-neutral-500 dark:text-neutral-400">Achievements</flux:text>
                        <flux:heading size="xl">{{ $achievementsEarned }}/{{ $totalAchievements }}</flux:heading>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Period Stats -->
        <div class="grid gap-4 sm:grid-cols-3">
            <div class="rounded-lg border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-800">
                <flux:text class="text-sm text-neutral-500 dark:text-neutral-400">Coins This Week</flux:text>
                <flux:heading size="lg" class="mt-2">{{ number_format($coinsThisWeek) }}</flux:heading>
            </div>

            <div class="rounded-lg border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-800">
                <flux:text class="text-sm text-neutral-500 dark:text-neutral-400">Coins This Month</flux:text>
                <flux:heading size="lg" class="mt-2">{{ number_format($coinsThisMonth) }}</flux:heading>
            </div>

            <div class="rounded-lg border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-800">
                <flux:text class="text-sm text-neutral-500 dark:text-neutral-400">Favorite Room</flux:text>
                <flux:heading size="lg" class="mt-2">{{ $favoriteRoom }}</flux:heading>
            </div>
        </div>
    </div>
</div>
