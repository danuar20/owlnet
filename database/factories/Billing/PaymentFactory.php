<?php

declare(strict_types=1);

namespace Database\Factories\Billing;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentStatus;
use App\Models\Billing\Payment;
use App\Models\Billing\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'invoice_no' => 'INV-'.now()->format('Ym').'-'.strtoupper(fake()->bothify('?????')),
            'subtotal' => 200000,
            'tax_percent' => 0,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'promo_code' => null,
            'promo_amount' => 0,
            'amount' => 200000,
            'method' => 'cash',
            'status' => PaymentStatus::PENDING,
            'invoice_status' => InvoiceStatus::DRAFT,
            'due_date' => now()->addDays(7),
        ];
    }

    public function paid(): static
    {
        return $this->state(fn () => [
            'invoice_status' => InvoiceStatus::PAID,
            'status' => PaymentStatus::PAID,
            'paid_at' => now(),
        ]);
    }

    public function sent(): static
    {
        return $this->state(fn () => ['invoice_status' => InvoiceStatus::SENT]);
    }
}
