<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Billing\Package;
use App\Repositories\Billing\PackageRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

/**
 * Domain service for Internet package (plan) management.
 */
class PackageService extends BaseService
{
    public function __construct(
        private readonly PackageRepository $packages
    ) {}

    /**
     * @return Collection<int, Package>
     */
    public function list(): Collection
    {
        return $this->packages->all();
    }

    /**
     * @return Collection<int, Package>
     */
    public function listActive(): Collection
    {
        return $this->packages->active();
    }

    public function find(string $id): ?Package
    {
        return $this->packages->find($id);
    }

    public function findOrFail(string $id): Package
    {
        return $this->packages->findOrFail($id);
    }

    /**
     * Create a package. Generates a unique code when none is supplied.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Package
    {
        $data['code'] = $data['code'] ?? $this->generateCode($data['name'] ?? 'PKG');

        return $this->packages->create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(string $id, array $data): Package
    {
        return $this->packages->update($id, $data);
    }

    public function delete(string $id): bool
    {
        return $this->packages->delete($id);
    }

    /**
     * Build a unique, human-friendly package code from the name.
     */
    private function generateCode(string $name): string
    {
        $base = Str::upper(Str::slug($name, ''));
        $base = $base !== '' ? Str::substr($base, 0, 8) : 'PKG';

        do {
            $code = $base.'-'.Str::upper(Str::random(4));
        } while ($this->packages->findByCode($code) !== null);

        return $code;
    }
}
