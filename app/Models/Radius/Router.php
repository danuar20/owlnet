<?php

declare(strict_types=1);

namespace App\Models\Radius;

use App\Models\Billing\Subscription;
use Database\Factories\Radius\RouterFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * MikroTik / FreeRADIUS router / NAS (radius schema).
 *
 * @property string $id
 * @property string $name
 * @property string $ip_address
 * @property int|null $api_port
 * @property string|null $username
 * @property string|null $password
 * @property string|null $radius_secret
 * @property string|null $nas_identifier
 * @property string|null $location
 * @property string $api_type
 * @property bool $is_active
 * @property string $status
 * @property Carbon|null $last_seen_at
 */
class Router extends Model
{
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    /** Status values. */
    public const STATUS_ONLINE = 'online';

    public const STATUS_OFFLINE = 'offline';

    public const STATUS_INACTIVE = 'inactive';

    public const STATUSES = [
        self::STATUS_ONLINE,
        self::STATUS_OFFLINE,
        self::STATUS_INACTIVE,
    ];

    protected $table = 'radius.routers';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'name',
        'ip_address',
        'api_port',
        'username',
        'password',
        'radius_secret',
        'nas_identifier',
        'location',
        'api_type',
        'is_active',
        'status',
        'last_seen_at',
        'remarks',
    ];

    protected $hidden = [
        'password',
        'radius_secret',
    ];

    protected $casts = [
        'api_port' => 'integer',
        'is_active' => 'boolean',
        'last_seen_at' => 'datetime',
    ];

    protected $attributes = [
        'api_type' => 'mikrotik',
        'status' => self::STATUS_INACTIVE,
    ];

    /** @return HasMany<MikrotikProfile> */
    public function mikrotikProfiles(): HasMany
    {
        return $this->hasMany(MikrotikProfile::class, 'router_id');
    }

    /** @return HasMany<Subscription> */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'router_id');
    }

    /** @param Builder<Router> $query */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /** @param Builder<Router> $query */
    public function scopeOnline(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ONLINE);
    }

    public function isOnline(): bool
    {
        return $this->status === self::STATUS_ONLINE;
    }

    /**
     * Bootstrap badge class for the current status.
     */
    public function statusColor(): string
    {
        return match ($this->status) {
            self::STATUS_ONLINE => 'success',
            self::STATUS_OFFLINE => 'danger',
            default => 'secondary',
        };
    }

    protected static function newFactory(): RouterFactory
    {
        return RouterFactory::new();
    }
}
