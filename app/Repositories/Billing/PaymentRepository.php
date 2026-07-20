<?php

declare(strict_types=1);

namespace App\Repositories\Billing;

use App\Models\Billing\Payment;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Collection;

/**
 * Persistence layer for invoices/payments (billing.payments).
 */
class PaymentRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new Payment);
    }

    /**
     * @return Collection<int, Payment>
     */
    public function all(): Collection
    {
        return $this->query()->orderByDesc('created_at')->get();
    }

    /**
     * @return Collection<int, Payment>
     */
    public function byUser(string $userId): Collection
    {
        return $this->query()->where('user_id', $userId)->orderByDesc('created_at')->get();
    }
}
