<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\GroupInvite;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

final class CleanupExpiredInvites implements ShouldQueue
{
    use Queueable;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        GroupInvite::query()
            ->expired()
            ->delete();
    }
}
