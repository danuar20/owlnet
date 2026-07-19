<?php

declare(strict_types=1);

namespace App\Models\Billing;

use App\Models\User as StaffUser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Customer / subscriber (billing schema).
 *
 * Distinct from App\Models\User (application staff in public.users).
 *
 * @property string $id
 * @property string $name
 * @property string|null $email
 * @property string|null $phone
 * @property string|null $address
 * @property string $status
 * @property string|null $remarks
 * @property string|null $created_by
 */
class User extends Model
{
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    protected $table = 'billing.users';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'status',
        'remarks',
        'created_by',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    /** @return HasMany<Subscription> */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'user_id');
    }

    /** @return HasMany<Payment> */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'user_id');
    }

    /** Staff member (public.users) who created this record. */
    public function creator()
    {
        return $this->belongsTo(StaffUser::class, 'created_by');
    }

    /** @param Builder<User> $query */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }
}
