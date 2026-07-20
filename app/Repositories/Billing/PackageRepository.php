<?php

declare(strict_types=1);

namespace App\Repositories\Billing;

use App\Models\Billing\Package;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Collection;

/**
 * Persistence layer for the Package aggregate (billing.packages).
 */
class PackageRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new Package);
    }

    /**
     * All packages, ordered by name.
     *
     * @return Collection<int, Package>
     */
    public function all(): Collection
    {
        return $this->query()->orderBy('name')->get();
    }

    /**
     * Only active packages.
     *
     * @return Collection<int, Package>
     */
    public function active(): Collection
    {
        return $this->query()->active()->orderBy('price')->get();
    }

    /**
     * Find a package by its unique code.
     */
    public function findByCode(string $code): ?Package
    {
        return $this->query()->where('code', $code)->first();
    }
}
