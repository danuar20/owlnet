<?php

declare(strict_types=1);

namespace Database\Factories\Billing;

use App\Enums\SubscriptionStatus;
use App\Models\Billing\Package;
use App\Models\Billing\Subscription;
use App\Models\Billing\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Subscription>
 */
class SubscriptionFactory extends Factory
{
    protected $model = Subscription::class;

    public function definition(): array
    {
        $user = User::factory()->create();
        $package = Package::factory()->create();

        return [
            'user_id' => $user->id,
            'package_id' => $package->id,
            'router_id' => null,
            'username' => 'user'.Str::random(6),
            'status' => SubscriptionStatus::PENDING,
            'started_at' => null,
            'expired_at' => null,
            'price' => $package->price,
            'remarks' => null,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn () => ['status' => SubscriptionStatus::PENDING]);
    }

    public function active(): static
    {
        return $this->state(fn () => [
            'status' => SubscriptionStatus::ACTIVE,
            'started_at' => now(),
            'expired_at' => now()->addDays(30),
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn () => [
            'status' => SubscriptionStatus::EXPIRED,
            'started_at' => now()->subDays(60),
            'expired_at' => now()->subDays(30),
        ]);
    }

    public function suspended(): static
    {
        return $this->state(fn () => ['status' => SubscriptionStatus::SUSPENDED]);
    }
}
