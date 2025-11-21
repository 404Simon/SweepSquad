<?php

declare(strict_types=1);

use App\Jobs\CleanupExpiredInvites;
use App\Jobs\UpdateUserStreaksJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule background jobs
Schedule::job(new UpdateUserStreaksJob)->dailyAt('00:00')->name('update-user-streaks');
Schedule::job(new CleanupExpiredInvites)->daily()->name('cleanup-expired-invites');
