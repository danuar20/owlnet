<?php

declare(strict_types=1);

namespace Database\Factories\Billing;

use App\Models\Billing\Package;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Package>
 */
class PackageFactory extends Factory
{
    protected $model = Package::class;

    public function definition(): array
    {
        $speed = fake()->randomElement(['5', '10', '20', '50', '100']);

        return [
            'name' => 'Paket '.$speed.'Mbps',
            'code' => 'PKG-'.strtoupper(Str::random(6)),
            'price' => fake()->randomElement([50000, 100000, 150000, 250000, 500000]),
            'duration_days' => fake()->randomElement([7, 30, 90, 365]),
            'speed_download' => $speed.'M',
            'speed_upload' => $speed.'M',
            'radius_profile' => 'profile-'.$speed.'m',
            'description' => fake()->sentence(),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
