<?php

declare(strict_types=1);

namespace Database\Factories\Billing;

use App\Models\Billing\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->numerify('08##########'),
            'address' => fake()->address(),
            'status' => 'active',
            'remarks' => null,
            'created_by' => null,
        ];
    }
}
