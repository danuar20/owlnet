<?php

declare(strict_types=1);

namespace App\Models\Billing;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One row per subscription status change (audit trail).
 *
 * @property string $id
 * @property string $subscription_id
 * @property string|null $from_status
 * @property string $to_status
 * @property string $action
 * @property string|null $note
 * @property string|null $changed_by
 */
class SubscriptionHistory extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'billing.subscription_histories';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = true;

    protected $fillable = [
        'subscription_id',
        'from_status',
        'to_status',
        'action',
        'note',
        'changed_by',
    ];

    /** @return BelongsTo<Subscription> */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class, 'subscription_id');
    }

    /** @param Builder<SubscriptionHistory> $query */
    public function scopeLatest(Builder $query): Builder
    {
        return $query->orderByDesc('created_at');
    }
}
