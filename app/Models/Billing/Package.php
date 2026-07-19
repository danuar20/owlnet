<?php

declare(strict_types=1);

namespace App\Models\Billing;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Internet package / plan (billing schema).
 *
 * @property string $id
 * @property string $name
 * @property string $code
 * @property string $price
 * @property int $duration_days
 * @property string|null $speed_download
 * @property string|null $speed_upload
 * @property string|null $description
 * @property bool $is_active
 */
class Package extends Model
{
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    protected $table = 'billing.packages';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'name',
        'code',
        'price',
        'duration_days',
        'speed_download',
        'speed_upload',
        'description',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'duration_days' => 'integer',
        'is_active' => 'boolean',
    ];

    /** @return HasMany<Subscription> */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'package_id');
    }

    /** @param Builder<Package> $query */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
