<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Billing\Subscription;
use App\Services\RadiusService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Provision (or update) the FreeRADIUS user for an active subscription.
 * Runs on the redis queue so the web request stays snappy.
 */
class ProvisionRadiusJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly string $subscriptionId,
        public readonly string $username,
        public readonly string $password,
        public readonly ?string $radiusProfile = null,
        public readonly ?string $rateLimit = null
    ) {}

    public function handle(RadiusService $radius): void
    {
        $replyAttributes = [];
        if ($this->radiusProfile !== null) {
            $replyAttributes['Mikrotik-Rate-Limit'] = $this->radiusProfile;
        } elseif ($this->rateLimit !== null) {
            $replyAttributes['Mikrotik-Rate-Limit'] = $this->rateLimit;
        }

        $radius->createUser(
            username: $this->username,
            password: $this->password,
            group: $this->radiusProfile,
            replyAttributes: $replyAttributes
        );

        // touch so the model is marked provisioned
        Subscription::where('id', $this->subscriptionId)->update([]);
    }
}
