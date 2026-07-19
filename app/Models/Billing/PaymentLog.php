<?php

declare(strict_types=1);

namespace App\Models\Billing;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Append-only payment event / gateway callback log (billing schema).
 *
 * No SoftDeletes: immutable audit trail.
 *
 * @property string $id
 * @property string $payment_id
 * @property string|null $user_id
 * @property string $level
 * @property string $event
 * @property string|null $message
 * @property mixed|null $payload
 */
class PaymentLog extends Model
{
    use HasFactory;
    use HasUuids;

    public $timestamps = true;

    protected $table = 'billing.payment_logs';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'payment_id',
        'user_id',
        'level',
        'event',
        'message',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    /** @return BelongsTo<Payment> */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class, 'payment_id');
    }

    /** @return BelongsTo<User> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
