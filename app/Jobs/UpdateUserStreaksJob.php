<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

final class UpdateUserStreaksJob implements ShouldQueue
{
    use Queueable;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Get all users who have cleaned at least once
        User::query()
            ->whereNotNull('last_cleaned_at')
            ->where('current_streak', '>', 0)
            ->chunk(100, function ($users): void {
                foreach ($users as $user) {
                    // Reset streak if last cleaned more than 24 hours ago (not yesterday or today)
                    if ($user->last_cleaned_at->lt(now()->subDay()->startOfDay())) {
                        $user->resetStreak();
                    }
                }
            });
    }
}
