<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seed the three operational roles with one account each.
 *
 * Password for every seeded account is "password" (change after first login).
 */
class RolesSeeder extends Seeder
{
    /**
     * Run the seeder.
     */
    public function run(): void
    {
        $accounts = [
            [
                'name' => 'Super Admin',
                'email' => 'superadmin@hermes.isp',
                'role' => UserRole::SUPER_ADMIN,
            ],
            [
                'name' => 'Admin',
                'email' => 'admin@hermes.isp',
                'role' => UserRole::ADMIN,
            ],
            [
                'name' => 'Operator',
                'email' => 'operator@hermes.isp',
                'role' => UserRole::OPERATOR,
            ],
        ];

        foreach ($accounts as $account) {
            User::firstOrCreate(
                ['email' => $account['email']],
                [
                    'name' => $account['name'],
                    'password' => 'password',
                    'role' => $account['role'],
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}
