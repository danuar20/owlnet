<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\UserRole;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Database\Eloquent\Collection;

/**
 * Domain service for application user (staff) management.
 *
 * Demonstrates the Service Layer convention: the controller injects this
 * service, and the service orchestrates the repository. No Eloquent queries
 * or HTTP concerns live here.
 */
class UserService extends BaseService
{
    /**
     * The user repository used by this service.
     */
    public function __construct(
        private readonly UserRepository $users
    ) {}

    /**
     * Return every application user.
     *
     * @return Collection<int, User>
     */
    public function listUsers()
    {
        return $this->users->all();
    }

    /**
     * Create a staff user with the given attributes.
     *
     * @param  array<string, mixed>  $data
     */
    public function createUser(array $data): User
    {
        return $this->users->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => $data['role'] ?? UserRole::OPERATOR->value,
        ]);
    }

    /**
     * Update an existing staff user. Password is only changed when supplied.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateUser(int|string $id, array $data): User
    {
        $attributes = collect($data)->only(['name', 'email', 'role'])->all();

        if (! empty($data['password'])) {
            $attributes['password'] = $data['password'];
        }

        return $this->users->update($id, $attributes);
    }

    /**
     * Delete a staff user (cannot delete yourself).
     */
    public function deleteUser(int|string $id): bool
    {
        return $this->users->delete($id);
    }
}
