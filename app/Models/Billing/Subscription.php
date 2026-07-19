<?php

declare(strict_types=1);

namespace App\Models\Billing;

use App\Models\Radius\Router;
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
 * @property string $status
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
        'status',
        'started_at',
        'expired_at',
        'price',
        'remarks',
    ];

    protected $casts = [
        'status' => 'string',
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

    /** @param Builder<Subscription> $query */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    /** @param Builder<Subscription> $query */
    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('expired_at', '<=', now());
    }
}
