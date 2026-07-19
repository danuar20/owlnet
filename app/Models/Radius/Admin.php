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
 * FreeRADIUS / appliance admin account (radius schema).
 *
 * Distinct from App\Models\User (application staff, public.users).
 *
 * @property string $id
 * @property string $username
 * @property string $password
 * @property string|null $email
 * @property bool $is_active
 * @property Carbon|null $last_login_at
 * @property string|null $created_by
 */
class Admin extends Model
{
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    protected $table = 'radius.admins';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'username',
        'password',
        'email',
        'is_active',
        'last_login_at',
        'created_by',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
    ];

    /** @return HasMany<ActivityLog> */
    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class, 'admin_id');
    }

    /** @param Builder<Admin> $query */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
