<?php

declare(strict_types=1);

namespace App\Models\Radius;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * FreeRADIUS `radgroupreply` row — one reply attribute for a group.
 *
 * A "Radius Profile" in the UI is the set of radgroupreply rows sharing a
 * groupname (e.g. rate-limit, session-timeout). Written to the live public
 * schema so FreeRADIUS applies them to members of the group.
 *
 * @property int $id
 * @property string $groupname
 * @property string $attribute
 * @property string $op
 * @property string $value
 */
class RadiusGroupReply extends Model
{
    use HasFactory;

    protected $table = 'public.radgroupreply';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'groupname',
        'attribute',
        'op',
        'value',
    ];

    /** @param Builder<RadiusGroupReply> $query */
    public function scopeForGroup(Builder $query, string $groupname): Builder
    {
        return $query->where('groupname', $groupname);
    }
}
