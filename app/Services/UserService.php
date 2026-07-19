<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Database\Eloquent\Collection;

/**
 * Domain service for user-related operations.
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
}
