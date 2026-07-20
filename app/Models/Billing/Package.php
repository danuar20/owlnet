<?php

declare(strict_types=1);

namespace App\Models\Billing;

use Database\Factories\Billing\PackageFactory;
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
 * @property string|null $radius_profile
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
        'radius_profile',
        'description',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'duration_days' => 'integer',
        'is_active' => 'boolean',
    ];

    protected $attributes = [
        'is_active' => true,
        'duration_days' => 30,
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

    /**
     * Human status label.
     */
    public function statusLabel(): string
    {
        return $this->is_active ? 'Active' : 'Inactive';
    }

    /**
     * Bootstrap badge colour for the status.
     */
    public function statusColor(): string
    {
        return $this->is_active ? 'success' : 'secondary';
    }

    /**
     * "10M/10M" rate-limit string (MikroTik style) from up/down speeds.
     */
    public function rateLimit(): ?string
    {
        if ($this->speed_upload === null && $this->speed_download === null) {
            return null;
        }

        return sprintf('%s/%s', $this->speed_upload ?? '0', $this->speed_download ?? '0');
    }

    protected static function newFactory(): PackageFactory
    {
        return PackageFactory::new();
    }
}
