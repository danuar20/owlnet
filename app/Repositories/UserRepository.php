<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\User;

/**
 * Persistence layer for the User aggregate root.
 *
 * This is a placeholder repository demonstrating the Repository pattern for
 * the default Laravel User model. Billing-specific repositories (customers,
 * packages, vouchers, sessions) will follow the same structure.
 */
class UserRepository extends BaseRepository
{
    /**
     * Create the repository bound to the User model.
     */
    public function __construct()
    {
        parent::__construct(new User);
    }
}
