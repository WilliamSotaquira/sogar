<?php

namespace App\Console;

use App\Jobs\ComputeShoppingMetrics;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        $schedule->job(new ComputeShoppingMetrics())
            ->weeklyOn(1, '03:00')
            ->withoutOverlapping()
            ->onOneServer();

        $schedule->command('recurrences:run')
            ->dailyAt('03:10')
            ->withoutOverlapping();
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
