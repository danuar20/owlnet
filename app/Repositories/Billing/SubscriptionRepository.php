<?php

declare(strict_types=1);

namespace App\Repositories\Billing;

use App\Enums\SubscriptionStatus;
use App\Models\Billing\Subscription;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Collection;

/**
 * Persistence layer for subscriptions (billing.subscriptions).
 */
class SubscriptionRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new Subscription);
    }

    /**
     * @return Collection<int, Subscription>
     */
    public function all(): Collection
    {
        return $this->query()->orderByDesc('created_at')->get();
    }

    /**
     * @return Collection<int, Subscription>
     */
    public function active(): Collection
    {
        return $this->query()->where('status', SubscriptionStatus::ACTIVE)->get();
    }

    public function countByStatus(): array
    {
        return $this->query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->all();
    }
}
