<?php

declare(strict_types=1);

namespace App\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Abstract base repository providing generic Eloquent persistence operations.
 *
 * Concrete repositories extend this class and type-hint their model so that
 * service classes can remain completely unaware of the storage layer.
 */
abstract class BaseRepository implements RepositoryInterface
{
    /**
     * The model instance handled by this repository.
     */
    protected Model $model;

    /**
     * Create the repository and resolve the underlying model instance.
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Return a new query builder instance for the model.
     */
    protected function query()
    {
        return $this->model->newQuery();
    }

    /**
     * {@inheritDoc}
     */
    public function all(): Collection
    {
        return $this->query()->get();
    }

    /**
     * {@inheritDoc}
     */
    public function find(mixed $id): ?Model
    {
        return $this->query()->find($id);
    }

    /**
     * {@inheritDoc}
     */
    public function findOrFail(mixed $id): Model
    {
        return $this->query()->findOrFail($id);
    }

    /**
     * {@inheritDoc}
     */
    public function create(array $attributes): Model
    {
        return $this->query()->create($attributes);
    }

    /**
     * {@inheritDoc}
     */
    public function update(mixed $id, array $attributes): Model
    {
        $record = $this->findOrFail($id);
        $record->update($attributes);

        return $record->fresh();
    }

    /**
     * {@inheritDoc}
     */
    public function delete(mixed $id): bool
    {
        return (bool) $this->findOrFail($id)->delete();
    }
}
