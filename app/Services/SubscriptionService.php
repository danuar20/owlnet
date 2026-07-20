<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\SubscriptionStatus;
use App\Jobs\ProvisionRadiusJob;
use App\Jobs\SuspendRadiusJob;
use App\Models\Billing\Subscription;
use App\Models\Billing\SubscriptionHistory;
use App\Repositories\Billing\SubscriptionRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Lifecycle management for customer subscriptions: activate, suspend, renew,
 * expire (manual + auto), cancel. Each state change is written to the
 * subscription_histories audit trail and, when the subscription has a RADIUS
 * username, a queued job syncs the FreeRADIUS side.
 */
class SubscriptionService
{
    public function __construct(
        private readonly SubscriptionRepository $subscriptions
    ) {}

    /**
     * @return Collection<int, Subscription>
     */
    public function list(): Collection
    {
        return $this->subscriptions->all();
    }

    public function find(string $id): ?Subscription
    {
        return $this->subscriptions->find($id);
    }

    public function findOrFail(string $id): Subscription
    {
        return $this->subscriptions->findOrFail($id);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Subscription
    {
        return $this->subscriptions->create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(string $id, array $data): Subscription
    {
        return $this->subscriptions->update($id, $data);
    }

    /**
     * Activate a subscription: provision RADIUS, set started_at and expiry,
     * and dispatch the provisioning job. Records history.
     *
     * @param  array{username?: string, password?: string, started_at?: string, expired_at?: string}  $input
     */
    public function activate(Subscription $subscription, array $input = [], ?string $changedBy = null): Subscription
    {
        return DB::transaction(function () use ($subscription, $input, $changedBy): Subscription {
            $username = $input['username'] ?? $subscription->username;
            $password = $input['password'] ?? null;

            $subscription->username = $username;
            $subscription->status = SubscriptionStatus::ACTIVE;
            $subscription->started_at = $input['started_at'] ?? now();
            $subscription->expired_at = $input['expired_at']
                ?? now()->addDays((int) ($subscription->package?->duration_days ?? 30));

            $subscription->save();

            $this->recordHistory($subscription, SubscriptionStatus::ACTIVE, 'activate', $changedBy);

            if ($username !== null && $username !== '' && $password !== null) {
                ProvisionRadiusJob::dispatch(
                    $subscription->id,
                    $username,
                    $password,
                    $subscription->package?->radius_profile,
                    $subscription->package?->rateLimit()
                );
            }

            return $subscription;
        });
    }

    /**
     * Suspend a subscription (disables RADIUS access). Records history.
     */
    public function suspend(Subscription $subscription, ?string $changedBy = null): Subscription
    {
        return DB::transaction(function () use ($subscription, $changedBy): Subscription {
            $from = $subscription->status;
            $subscription->status = SubscriptionStatus::SUSPENDED;
            $subscription->save();

            $this->recordHistory($subscription, $from, 'suspend', $changedBy);

            if ($subscription->username !== null && $subscription->username !== '') {
                SuspendRadiusJob::dispatch($subscription->username);
            }

            return $subscription;
        });
    }

    /**
     * Renew a subscription: extend the expiry and reactivate if suspended/expired.
     * Records history.
     *
     * @param  array{expired_at?: string, days?: int}  $input
     */
    public function renew(Subscription $subscription, array $input = [], ?string $changedBy = null): Subscription
    {
        return DB::transaction(function () use ($subscription, $input, $changedBy): Subscription {
            $from = $subscription->status;

            $base = ($subscription->expired_at !== null && $subscription->expired_at->isFuture())
                ? $subscription->expired_at
                : now();

            $days = $input['days'] ?? (int) ($subscription->package?->duration_days ?? 30);
            $subscription->expired_at = $input['expired_at'] ?? $base->addDays($days);
            $subscription->status = SubscriptionStatus::ACTIVE;
            $subscription->save();

            $this->recordHistory($subscription, $from, 'renew', $changedBy);

            if ($subscription->username !== null && $subscription->username !== '') {
                ProvisionRadiusJob::dispatch(
                    $subscription->id,
                    $subscription->username,
                    $input['password'] ?? (string) Str::random(12),
                    $subscription->package?->radius_profile,
                    $subscription->package?->rateLimit()
                );
            }

            return $subscription;
        });
    }

    /**
     * Mark a single subscription expired. Records history.
     */
    public function expire(Subscription $subscription, ?string $changedBy = null): Subscription
    {
        return DB::transaction(function () use ($subscription, $changedBy): Subscription {
            $from = $subscription->status;
            $subscription->status = SubscriptionStatus::EXPIRED;
            $subscription->save();

            $this->recordHistory($subscription, $from, 'expire', $changedBy);

            if ($subscription->username !== null && $subscription->username !== '') {
                SuspendRadiusJob::dispatch($subscription->username);
            }

            return $subscription;
        });
    }

    /**
     * Auto-expire every active subscription whose expiry has passed.
     * Used by the scheduler (subscriptions:expire-due). Records history per row.
     *
     * @return int number of subscriptions expired
     */
    public function expireDue(?string $changedBy = null): int
    {
        $due = Subscription::dueForExpiry()->get();
        $count = 0;

        foreach ($due as $subscription) {
            $this->expire($subscription, null);
            $count++;
        }

        return $count;
    }

    /**
     * Cancel a subscription (removes RADIUS access, keeps the record).
     */
    public function cancel(Subscription $subscription, ?string $changedBy = null): Subscription
    {
        return DB::transaction(function () use ($subscription, $changedBy): Subscription {
            $from = $subscription->status;
            $subscription->status = SubscriptionStatus::EXPIRED;
            $subscription->save();

            $this->recordHistory($subscription, $from, 'cancel', $changedBy);

            if ($subscription->username !== null && $subscription->username !== '') {
                SuspendRadiusJob::dispatch($subscription->username);
            }

            return $subscription;
        });
    }

    /**
     * @return Collection<int, SubscriptionHistory>
     */
    public function history(Subscription $subscription): Collection
    {
        return $subscription->histories;
    }

    private function recordHistory(Subscription $subscription, ?SubscriptionStatus $from, string $action, ?string $changedBy): void
    {
        SubscriptionHistory::create([
            'subscription_id' => $subscription->id,
            'from_status' => $from?->value,
            'to_status' => $subscription->status->value,
            'action' => $action,
            'changed_by' => $changedBy,
        ]);
    }
}
