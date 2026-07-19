<?php

declare(strict_types=1);

namespace App\Models\Radius;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * FreeRADIUS `radusergroup` row — maps a user to a group with a priority.
 *
 * Group membership lets FreeRADIUS apply shared radgroupcheck / radgroupreply
 * attributes. Lower priority values are evaluated first.
 *
 * @property int $id
 * @property string $username
 * @property string $groupname
 * @property int $priority
 */
class RadiusGroup extends Model
{
    use HasFactory;

    protected $table = 'public.radusergroup';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'username',
        'groupname',
        'priority',
    ];

    protected $attributes = [
        'priority' => 0,
    ];

    protected $casts = [
        'priority' => 'integer',
    ];

    /** @param Builder<RadiusGroup> $query */
    public function scopeForUsername(Builder $query, string $username): Builder
    {
        return $query->where('username', $username);
    }

    /** @param Builder<RadiusGroup> $query */
    public function scopeForGroup(Builder $query, string $groupname): Builder
    {
        return $query->where('groupname', $groupname);
    }

    /** @param Builder<RadiusGroup> $query */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('priority');
    }
}
