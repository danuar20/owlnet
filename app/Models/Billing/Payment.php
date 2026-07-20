<?php

declare(strict_types=1);

namespace App\Models\Billing;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * Payment / Invoice for a subscription (billing schema).
 *
 * An invoice is represented by this row: line items live in
 * billing.invoice_items, and the grand total is derived from
 * subtotal + tax - discount - promo.
 *
 * @property string $id
 * @property string $user_id
 * @property string|null $subscription_id
 * @property string|null $invoice_no
 * @property string $amount
 * @property string $subtotal
 * @property string $tax_percent
 * @property string $tax_amount
 * @property string $discount_amount
 * @property string|null $promo_code
 * @property string $promo_amount
 * @property Carbon|null $due_date
 * @property InvoiceStatus $invoice_status
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
        'subtotal',
        'tax_percent',
        'tax_amount',
        'discount_amount',
        'promo_code',
        'promo_amount',
        'method',
        'gateway',
        'status',
        'invoice_status',
        'due_date',
        'paid_at',
        'notes',
        'remarks',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'tax_percent' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'promo_amount' => 'decimal:2',
        'status' => PaymentStatus::class,
        'invoice_status' => InvoiceStatus::class,
        'paid_at' => 'datetime',
        'due_date' => 'datetime',
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

    /** @return HasMany<InvoiceItem> */
    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class, 'invoice_id')->ordered();
    }

    /** @param Builder<Payment> $query */
    public function scopePaid(Builder $query): Builder
    {
        return $query->where('status', PaymentStatus::PAID);
    }

    /** @param Builder<Payment> $query */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', PaymentStatus::PENDING);
    }

    /**
     * Recompute subtotal from line items and the grand total from
     * subtotal + tax - discount - promo. Persists the derived columns.
     */
    public function recalculate(): static
    {
        $subtotal = $this->items->sum(fn (InvoiceItem $i) => (float) $i->amount);

        $taxAmount = $subtotal * ((float) $this->tax_percent / 100);
        $total = $subtotal + $taxAmount - (float) $this->discount_amount - (float) $this->promo_amount;
        if ($total < 0) {
            $total = 0;
        }

        $this->subtotal = $subtotal;
        $this->tax_amount = $taxAmount;
        $this->amount = $total;

        return $this;
    }

    public function isPaid(): bool
    {
        return $this->invoice_status === InvoiceStatus::PAID;
    }
}
