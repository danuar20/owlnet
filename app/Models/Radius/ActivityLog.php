<?php

declare(strict_types=1);

namespace App\Models\Radius;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Immutable activity / audit log (radius schema).
 *
 * No SoftDeletes: append-only audit trail.
 *
 * @property string $id
 * @property string|null $admin_id
 * @property string|null $subject_type
 * @property string|null $subject_id
 * @property string $event
 * @property string|null $description
 * @property mixed|null $payload
 * @property string|null $ip_address
 * @property Carbon|null $created_at
 */
class ActivityLog extends Model
{
    use HasFactory;
    use HasUuids;

    public $timestamps = false;

    protected $table = 'radius.activity_logs';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'admin_id',
        'subject_type',
        'subject_id',
        'event',
        'description',
        'payload',
        'ip_address',
        'created_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'created_at' => 'datetime',
    ];

    /** @return BelongsTo<Admin> */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    /** @param Builder<ActivityLog> $query */
    public function scopeForSubject(Builder $query, string $type, string $id): Builder
    {
        return $query->where('subject_type', $type)->where('subject_id', $id);
    }
}
