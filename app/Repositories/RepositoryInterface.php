<?php

declare(strict_types=1);

namespace App\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Contract for read/write access to a single aggregate root.
 *
 * Service classes depend on this interface, never on a concrete repository
 * or on Eloquent directly, so the persistence implementation can be swapped
 * without touching business logic.
 */
interface RepositoryInterface
{
    /**
     * Return every record for the model.
     */
    public function all(): Collection;

    /**
     * Find a record by its primary key or return null.
     */
    public function find(mixed $id): ?Model;

    /**
     * Find a record by its primary key or throw an exception.
     */
    public function findOrFail(mixed $id): Model;

    /**
     * Persist a new record and return the created model.
     */
    public function create(array $attributes): Model;

    /**
     * Update an existing record and return the refreshed model.
     */
    public function update(mixed $id, array $attributes): Model;

    /**
     * Delete a record by its primary key.
     */
    public function delete(mixed $id): bool;
}
