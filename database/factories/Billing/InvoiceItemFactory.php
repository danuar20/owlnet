<?php

declare(strict_types=1);

namespace Database\Factories\Billing;

use App\Models\Billing\InvoiceItem;
use App\Models\Billing\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InvoiceItem>
 */
class InvoiceItemFactory extends Factory
{
    protected $model = InvoiceItem::class;

    public function definition(): array
    {
        $qty = fake()->numberBetween(1, 3);
        $unit = fake()->randomElement([100000, 150000, 200000, 299000]);

        return [
            'invoice_id' => Payment::factory(),
            'description' => fake()->randomElement(['Monthly fee', 'Installation', 'Router rental', 'Add-on bandwidth']),
            'quantity' => $qty,
            'unit_price' => $unit,
            'amount' => $qty * $unit,
            'sort_order' => 0,
        ];
    }
}
