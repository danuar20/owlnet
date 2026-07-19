<?php

declare(strict_types=1);

namespace App\Models\Radius;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * MikroTik PPP / Hotspot profile bound to a router (radius schema).
 *
 * @property string $id
 * @property string|null $router_id
 * @property string $name
 * @property string $profile_type
 * @property string|null $rate_limit
 * @property int|null $session_timeout
 * @property int|null $idle_timeout
 * @property bool $is_active
 */
class MikrotikProfile extends Model
{
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    protected $table = 'radius.mikrotik_profiles';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'router_id',
        'name',
        'profile_type',
        'rate_limit',
        'session_timeout',
        'idle_timeout',
        'is_active',
        'remarks',
    ];

    protected $casts = [
        'session_timeout' => 'integer',
        'idle_timeout' => 'integer',
        'is_active' => 'boolean',
    ];

    /** @return BelongsTo<Router> */
    public function router(): BelongsTo
    {
        return $this->belongsTo(Router::class, 'router_id');
    }

    /** @param Builder<MikrotikProfile> $query */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
