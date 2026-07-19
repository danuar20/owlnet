<?php

declare(strict_types=1);

namespace Database\Factories\Radius;

use App\Models\Radius\Router;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Router>
 */
class RouterFactory extends Factory
{
    protected $model = Router::class;

    public function definition(): array
    {
        return [
            'name' => 'Router-'.strtoupper(Str::random(5)),
            'ip_address' => fake()->unique()->ipv4(),
            'api_port' => 8728,
            'username' => 'admin',
            'password' => 'admin',
            'radius_secret' => Str::random(16),
            'nas_identifier' => 'nas-'.Str::lower(Str::random(6)),
            'location' => fake()->city(),
            'api_type' => 'mikrotik',
            'is_active' => true,
            'status' => Router::STATUS_INACTIVE,
            'remarks' => null,
        ];
    }

    public function online(): static
    {
        return $this->state(fn () => ['status' => Router::STATUS_ONLINE, 'is_active' => true]);
    }

    public function offline(): static
    {
        return $this->state(fn () => ['status' => Router::STATUS_OFFLINE]);
    }
}
