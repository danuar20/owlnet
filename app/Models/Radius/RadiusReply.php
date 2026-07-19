<?php

declare(strict_types=1);

namespace App\Models\Radius;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * FreeRADIUS `radreply` row — a per-user reply attribute.
 *
 * Reply attributes are returned to the NAS on Access-Accept, e.g.
 * (username, 'Framed-IP-Address', '=', '10.0.0.5') or a rate-limit AVP.
 *
 * @property int $id
 * @property string $username
 * @property string $attribute
 * @property string $op
 * @property string $value
 */
class RadiusReply extends Model
{
    use HasFactory;

    protected $table = 'public.radreply';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'username',
        'attribute',
        'op',
        'value',
    ];

    protected $attributes = [
        'op' => '=',
        'value' => '',
    ];

    /** @param Builder<RadiusReply> $query */
    public function scopeForUsername(Builder $query, string $username): Builder
    {
        return $query->where('username', $username);
    }

    /** @param Builder<RadiusReply> $query */
    public function scopeAttribute(Builder $query, string $attribute): Builder
    {
        return $query->where('attribute', $attribute);
    }
}
