<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Radius\Router;
use App\Repositories\Radius\RouterRepository;
use Illuminate\Database\Eloquent\Collection;

/**
 * Domain service for router (NAS) management.
 *
 * Handles CRUD plus reachability checks (ICMP ping + TCP connection test to
 * the router API port). No MikroTik/RouterOS protocol integration yet — the
 * connection test only proves the API port is reachable.
 */
class RouterService extends BaseService
{
    /** Default MikroTik API TCP port. */
    public const DEFAULT_API_PORT = 8728;

    public function __construct(
        private readonly RouterRepository $routers
    ) {}

    /**
     * List all routers (unlimited).
     *
     * @return Collection<int, Router>
     */
    public function list(): Collection
    {
        return $this->routers->all();
    }

    public function find(string $id): ?Router
    {
        return $this->routers->find($id);
    }

    public function findOrFail(string $id): Router
    {
        return $this->routers->findOrFail($id);
    }

    /**
     * Create a router.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Router
    {
        return $this->routers->create($data);
    }

    /**
     * Update a router.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(string $id, array $data): Router
    {
        return $this->routers->update($id, $data);
    }

    public function delete(string $id): bool
    {
        return $this->routers->delete($id);
    }

    /**
     * ICMP ping test. Returns latency in ms on success, or null on failure.
     */
    public function ping(Router $router, int $timeoutSeconds = 2): ?float
    {
        $ip = escapeshellarg($router->ip_address);
        $timeout = max(1, $timeoutSeconds);

        // -c 1 one packet, -W timeout (Linux). Suppress output; parse rtt.
        $command = sprintf('ping -c 1 -W %d %s 2>/dev/null', $timeout, $ip);
        exec($command, $output, $exitCode);

        if ($exitCode !== 0) {
            return null;
        }

        foreach ($output as $line) {
            if (preg_match('/time[=<]([\d.]+)\s*ms/', $line, $m)) {
                return (float) $m[1];
            }
        }

        // Reachable but no parseable rtt.
        return 0.0;
    }

    /**
     * TCP connection test against the router API port.
     */
    public function testConnection(Router $router, ?int $timeoutSeconds = 3): bool
    {
        $port = $router->api_port ?: self::DEFAULT_API_PORT;
        $errno = 0;
        $errstr = '';

        $conn = @fsockopen($router->ip_address, $port, $errno, $errstr, (float) ($timeoutSeconds ?? 3));

        if ($conn === false) {
            return false;
        }

        fclose($conn);

        return true;
    }

    /**
     * Run reachability checks and persist the resulting status.
     *
     * @return array{status: string, ping_ms: float|null, api_reachable: bool}
     */
    public function refreshStatus(Router $router): array
    {
        $pingMs = $this->ping($router);
        $apiReachable = $this->testConnection($router);

        $status = match (true) {
            ! $router->is_active => Router::STATUS_INACTIVE,
            $pingMs !== null || $apiReachable => Router::STATUS_ONLINE,
            default => Router::STATUS_OFFLINE,
        };

        $this->routers->updateStatus($router, $status);

        return [
            'status' => $status,
            'ping_ms' => $pingMs,
            'api_reachable' => $apiReachable,
        ];
    }
}
