<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\User;
use Database\Seeders\RolesSeeder;

test('roles seeder creates one account per role', function () {
    $this->seed(RolesSeeder::class);

    expect(User::where('role', UserRole::SUPER_ADMIN)->count())->toBe(1)
        ->and(User::where('role', UserRole::ADMIN)->count())->toBe(1)
        ->and(User::where('role', UserRole::OPERATOR)->count())->toBe(1);
});

test('seeded super admin has the expected credentials', function () {
    $this->seed(RolesSeeder::class);

    $admin = User::where('role', UserRole::SUPER_ADMIN)->first();

    expect($admin)->not->toBeNull()
        ->and($admin->email)->toBe('superadmin@hermes.isp')
        ->and(Hash::check('password', $admin->password))->toBeTrue();
});
