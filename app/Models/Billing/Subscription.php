<?php

declare(strict_types=1);

namespace App\Models\Billing;

use App\Enums\SubscriptionStatus;
use App\Models\Radius\Router;
use App\Services\RadiusService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * Customer subscription to a package (billing schema).
 *
 * @property string $id
 * @property string $user_id
 * @property string $package_id
 * @property string|null $router_id
 * @property string|null $username
 * @property SubscriptionStatus $status
 * @property Carbon|null $started_at
 * @property Carbon|null $expired_at
 * @property string $price
 */
class Subscription extends Model
{
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    protected $table = 'billing.subscriptions';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'package_id',
        'router_id',
        'username',
        'password',
        'status',
        'started_at',
        'expired_at',
        'price',
        'remarks',
    ];

    protected $casts = [
        'status' => SubscriptionStatus::class,
        'price' => 'decimal:2',
        'started_at' => 'datetime',
        'expired_at' => 'datetime',
    ];

    /** @return BelongsTo<User> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** @return BelongsTo<Package> */
    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class, 'package_id');
    }

    /** @return BelongsTo<Router> */
    public function router(): BelongsTo
    {
        return $this->belongsTo(Router::class, 'router_id');
    }

    /** @return HasMany<Payment> */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'subscription_id');
    }

    /** @return HasMany<SubscriptionHistory> */
    public function histories(): HasMany
    {
        return $this->hasMany(SubscriptionHistory::class, 'subscription_id')->latest();
    }

    /** @param Builder<Subscription> $query */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', SubscriptionStatus::ACTIVE);
    }

    /** @param Builder<Subscription> $query */
    public function scopeSuspended(Builder $query): Builder
    {
        return $query->where('status', SubscriptionStatus::SUSPENDED);
    }

    /** @param Builder<Subscription> $query */
    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('status', SubscriptionStatus::EXPIRED);
    }

    /** Subscriptions whose expiry has passed but aren't marked expired yet. */
    /** @param Builder<Subscription> $query */
    public function scopeDueForExpiry(Builder $query): Builder
    {
        return $query->where('status', SubscriptionStatus::ACTIVE)
            ->whereNotNull('expired_at')
            ->where('expired_at', '<=', now());
    }

    public function isActive(): bool
    {
        return $this->status === SubscriptionStatus::ACTIVE;
    }

    /**
     * Whole days remaining until expiry (negative if already past).
     */
    public function daysRemaining(): ?int
    {
        if ($this->expired_at === null) {
            return null;
        }

        return (int) now()->diffInDays($this->expired_at, false);
    }

    /**
     * Provision the RADIUS side for this subscription: create/update the
     * radcheck user, assign the package's FreeRADIUS group, and apply the
     * package rate-limit as a direct reply attribute. No-op if no username.
     */
    public function provisionRadius(string $password, ?string $changedBy = null): void
    {
        if ($this->username === null || $this->username === '') {
            return;
        }

        /** @var RadiusService $radius */
        $radius = app(RadiusService::class);

        $replyAttributes = [];
        if ($this->package?->radius_profile) {
            $replyAttributes['Mikrotik-Rate-Limit'] = $this->package->radius_profile;
        } elseif ($this->package?->rateLimit()) {
            $replyAttributes['Mikrotik-Rate-Limit'] = $this->package->rateLimit();
        }

        $radius->createUser(
            username: $this->username,
            password: $password,
            group: $this->package?->radius_profile, // FreeRADIUS group via radusergroup
            replyAttributes: $replyAttributes
        );
    }

    public function suspendRadius(): void
    {
        if ($this->username === null || $this->username === '') {
            return;
        }

        /** @var RadiusService $radius */
        $radius = app(RadiusService::class);
        $radius->deleteUser($this->username);
    }
}
