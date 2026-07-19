<?php

declare(strict_types=1);

namespace App\Models\Billing;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * Payment for a subscription (billing schema).
 *
 * @property string $id
 * @property string $user_id
 * @property string|null $subscription_id
 * @property string|null $invoice_no
 * @property string $amount
 * @property string $method
 * @property string|null $gateway
 * @property string $status
 * @property Carbon|null $paid_at
 */
class Payment extends Model
{
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    protected $table = 'billing.payments';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'subscription_id',
        'invoice_no',
        'amount',
        'method',
        'gateway',
        'status',
        'paid_at',
        'remarks',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'status' => 'string',
        'paid_at' => 'datetime',
    ];

    /** @return BelongsTo<User> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** @return BelongsTo<Subscription> */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class, 'subscription_id');
    }

    /** @return HasMany<PaymentLog> */
    public function logs(): HasMany
    {
        return $this->hasMany(PaymentLog::class, 'payment_id');
    }

    /** @param Builder<Payment> $query */
    public function scopePaid(Builder $query): Builder
    {
        return $query->where('status', 'paid');
    }

    /** @param Builder<Payment> $query */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }
}
