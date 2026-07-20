<?php

declare(strict_types=1);

use App\Console\Commands\ExpireDueSubscriptions;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function (): void {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Auto-expire subscriptions nightly (FreeRADIUS access is revoked via queue job).
Schedule::command(ExpireDueSubscriptions::class)->dailyAt('00:05');
