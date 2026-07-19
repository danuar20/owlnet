<?php

declare(strict_types=1);

namespace App\Models\Radius;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * MikroTik / FreeRADIUS router (radius schema).
 *
 * @property string $id
 * @property string $name
 * @property string $ip_address
 * @property int|null $api_port
 * @property string|null $username
 * @property string|null $password
 * @property string $api_type
 * @property bool $is_active
 * @property Carbon|null $last_seen_at
 */
class Router extends Model
{
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    protected $table = 'radius.routers';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'name',
        'ip_address',
        'api_port',
        'username',
        'password',
        'api_type',
        'is_active',
        'last_seen_at',
        'remarks',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'api_port' => 'integer',
        'is_active' => 'boolean',
        'last_seen_at' => 'datetime',
    ];

    /** @return HasMany<MikrotikProfile> */
    public function mikrotikProfiles(): HasMany
    {
        return $this->hasMany(MikrotikProfile::class, 'router_id');
    }

    /** @return HasMany<Subscription> */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(\App\Models\Billing\Subscription::class, 'router_id');
    }

    /** @param Builder<Router> $query */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
