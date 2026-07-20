<?php

declare(strict_types=1);

namespace App\Models\Billing;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One line item of an invoice (Invoice Detail).
 *
 * @property string $id
 * @property string $invoice_id
 * @property string $description
 * @property int $quantity
 * @property string $unit_price
 * @property string $amount
 * @property int $sort_order
 */
class InvoiceItem extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'billing.invoice_items';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'invoice_id',
        'description',
        'quantity',
        'unit_price',
        'amount',
        'sort_order',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'amount' => 'decimal:2',
        'quantity' => 'integer',
        'sort_order' => 'integer',
    ];

    /** @return BelongsTo<Payment> */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Payment::class, 'invoice_id');
    }

    /** @param Builder<InvoiceItem> $query */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('created_at');
    }
}
