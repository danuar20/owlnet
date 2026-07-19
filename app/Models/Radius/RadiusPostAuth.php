<?php

declare(strict_types=1);

namespace App\Models\Radius;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * FreeRADIUS `radpostauth` row — an authentication attempt audit record.
 *
 * The RADIUS server appends one row per Access-Request result (Access-Accept
 * or Access-Reject). Append-only audit table: never updated or soft-deleted.
 *
 * @property int $id
 * @property string $username
 * @property string|null $pass
 * @property string|null $reply
 * @property string|null $calledstationid
 * @property string|null $callingstationid
 * @property Carbon $authdate
 */
class RadiusPostAuth extends Model
{
    use HasFactory;

    /** Reply values FreeRADIUS records. */
    public const REPLY_ACCEPT = 'Access-Accept';

    public const REPLY_REJECT = 'Access-Reject';

    protected $table = 'public.radpostauth';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'username',
        'pass',
        'reply',
        'calledstationid',
        'callingstationid',
        'authdate',
    ];

    protected $casts = [
        'authdate' => 'datetime',
    ];

    /** @param Builder<RadiusPostAuth> $query */
    public function scopeForUsername(Builder $query, string $username): Builder
    {
        return $query->where('username', $username);
    }

    /** @param Builder<RadiusPostAuth> $query */
    public function scopeAccepted(Builder $query): Builder
    {
        return $query->where('reply', self::REPLY_ACCEPT);
    }

    /** @param Builder<RadiusPostAuth> $query */
    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('reply', self::REPLY_REJECT);
    }
}
