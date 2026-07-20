<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\SubscriptionService;
use Illuminate\Console\Command;

/**
 * Auto-expire subscriptions whose expiry date has passed.
 * Runs on a schedule (see routes/console.php).
 */
class ExpireDueSubscriptions extends Command
{
    protected $signature = 'subscriptions:expire-due';

    protected $description = 'Mark active subscriptions past their expiry date as expired (auto-expire).';

    public function handle(SubscriptionService $service): int
    {
        $count = $service->expireDue('scheduler');

        $this->info("Auto-expired {$count} subscription(s).");

        return self::SUCCESS;
    }
}
