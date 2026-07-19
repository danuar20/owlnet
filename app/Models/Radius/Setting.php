<?php

declare(strict_types=1);

namespace App\Models\Radius;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Key/value application settings (radius schema).
 *
 * @property string $id
 * @property string $key
 * @property string|null $value
 * @property string|null $group
 * @property bool $is_encrypted
 */
class Setting extends Model
{
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    protected $table = 'radius.settings';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'key',
        'value',
        'group',
        'is_encrypted',
        'description',
    ];

    protected $casts = [
        'is_encrypted' => 'boolean',
    ];

    /** @param Builder<Setting> $query */
    public function scopeGroup(Builder $query, string $group): Builder
    {
        return $query->where('group', $group);
    }
}
