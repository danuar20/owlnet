<?php

declare(strict_types=1);

namespace App\Repositories\Radius;

use App\Models\Radius\Router;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Collection;

/**
 * Persistence layer for the Router aggregate root (radius.routers).
 *
 * Supports an unlimited number of routers. All queries go through the base
 * repository so the service layer stays persistence-agnostic.
 */
class RouterRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new Router);
    }

    /**
     * All routers, newest first.
     *
     * @return Collection<int, Router>
     */
    public function all(): Collection
    {
        return $this->query()->orderBy('name')->get();
    }

    /**
     * Only active routers.
     *
     * @return Collection<int, Router>
     */
    public function active(): Collection
    {
        return $this->query()->active()->orderBy('name')->get();
    }

    /**
     * Find a router by its IP address.
     */
    public function findByIp(string $ip): ?Router
    {
        return $this->query()->where('ip_address', $ip)->first();
    }

    /**
     * Update the reachability status (and last_seen_at when online).
     */
    public function updateStatus(Router $router, string $status): Router
    {
        $router->status = $status;

        if ($status === Router::STATUS_ONLINE) {
            $router->last_seen_at = now();
        }

        $router->save();

        return $router->refresh();
    }
}
