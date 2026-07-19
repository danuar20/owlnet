<?php

declare(strict_types=1);

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Example queued job.
 *
 * Placeholder showing the standard Laravel job skeleton. Replace with real
 * billing jobs (e.g. synchronizing FreeRADIUS accounting, expiring vouchers)
 * as features are implemented. Implements ShouldQueue so it is pushed to the
 * configured queue connection (Redis in this project).
 */
class ExampleJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //
    }
}
