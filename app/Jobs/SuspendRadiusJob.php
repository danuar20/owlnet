<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Services\RadiusService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Remove the FreeRADIUS user when a subscription is suspended/cancelled.
 */
class SuspendRadiusJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly string $username
    ) {}

    public function handle(RadiusService $radius): void
    {
        if ($radius->userExists($this->username)) {
            $radius->deleteUser($this->username);
        }
    }
}
