<?php

declare(strict_types=1);

use App\Models\Radius\Router;
use App\Repositories\Radius\RouterRepository;
use App\Services\RouterService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->service = app(RouterService::class);
});

it('resolves RouterService from the container', function (): void {
    expect($this->service)->toBeInstanceOf(RouterService::class);
});

it('creates a router', function (): void {
    $router = $this->service->create([
        'name' => 'Core-1',
        'ip_address' => '192.168.10.1',
        'radius_secret' => 'topsecret',
        'nas_identifier' => 'core1',
        'location' => 'HQ',
    ]);

    expect($router)->toBeInstanceOf(Router::class)
        ->and($router->id)->toBeString()
        ->and($router->name)->toBe('Core-1')
        ->and($router->status)->toBe(Router::STATUS_INACTIVE);

    expect(Router::count())->toBe(1);
});

it('lists routers ordered by name', function (): void {
    Router::factory()->create(['name' => 'Zeta']);
    Router::factory()->create(['name' => 'Alpha']);

    $names = $this->service->list()->pluck('name')->all();
    expect($names)->toBe(['Alpha', 'Zeta']);
});

it('updates a router', function (): void {
    $router = Router::factory()->create(['location' => 'Old']);

    $updated = $this->service->update($router->id, ['location' => 'New Site']);

    expect($updated->location)->toBe('New Site');
});

it('soft-deletes a router', function (): void {
    $router = Router::factory()->create();

    expect($this->service->delete($router->id))->toBeTrue();
    expect(Router::count())->toBe(0)
        ->and(Router::withTrashed()->count())->toBe(1);
});

it('supports an unlimited number of routers', function (): void {
    Router::factory()->count(25)->create();
    expect($this->service->list())->toHaveCount(25);
});

it('hides secret fields from array/json output', function (): void {
    $router = Router::factory()->create(['radius_secret' => 'shh', 'password' => 'pw']);

    $array = $router->toArray();
    expect($array)->not->toHaveKey('radius_secret')
        ->and($array)->not->toHaveKey('password');
});

it('marks status inactive when the router is disabled', function (): void {
    $router = Router::factory()->offline()->create(['is_active' => false]);

    $result = $this->service->refreshStatus($router);

    expect($result['status'])->toBe(Router::STATUS_INACTIVE);
    expect($router->fresh()->status)->toBe(Router::STATUS_INACTIVE);
});

it('reports offline when host is unreachable', function (): void {
    // TEST-NET-1 (192.0.2.0/24, RFC 5737) is guaranteed unroutable.
    $router = Router::factory()->create([
        'ip_address' => '192.0.2.123',
        'api_port' => 8728,
        'is_active' => true,
    ]);

    $result = $this->service->refreshStatus($router);

    expect($result['api_reachable'])->toBeFalse()
        ->and($result['ping_ms'])->toBeNull()
        ->and($result['status'])->toBe(Router::STATUS_OFFLINE);
});

it('finds a router by ip via the repository', function (): void {
    Router::factory()->create(['ip_address' => '10.10.10.10']);

    $repo = app(RouterRepository::class);
    expect($repo->findByIp('10.10.10.10'))->not->toBeNull()
        ->and($repo->findByIp('1.1.1.1'))->toBeNull();
});

it('computes bootstrap status colors', function (): void {
    expect(Router::factory()->online()->make()->statusColor())->toBe('success')
        ->and(Router::factory()->offline()->make()->statusColor())->toBe('danger')
        ->and(Router::factory()->make(['status' => Router::STATUS_INACTIVE])->statusColor())->toBe('secondary');
});
