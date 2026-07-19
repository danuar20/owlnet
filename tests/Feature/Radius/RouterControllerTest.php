<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\Radius\Router;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function adminUser(): User
{
    return User::factory()->create(['role' => UserRole::ADMIN]);
}

it('lists routers on the index page', function (): void {
    $router = Router::factory()->create(['name' => 'Edge-1']);

    $this->actingAs(adminUser())
        ->get(route('routers.index'))
        ->assertOk()
        ->assertSee('Edge-1');
});

it('creates a router via the controller', function (): void {
    $response = $this->actingAs(adminUser())->post(route('routers.store'), [
        'name' => 'New-Router',
        'ip_address' => '172.16.0.1',
        'radius_secret' => 'sec',
        'nas_identifier' => 'nr1',
        'location' => 'Branch',
        'api_port' => 8728,
        'api_type' => 'mikrotik',
        'is_active' => '1',
        'status' => Router::STATUS_INACTIVE,
    ]);

    $router = Router::where('name', 'New-Router')->first();
    expect($router)->not->toBeNull();
    $response->assertRedirect(route('routers.show', $router));
});

it('validates required fields', function (): void {
    $this->actingAs(adminUser())
        ->post(route('routers.store'), ['name' => '', 'ip_address' => 'not-an-ip'])
        ->assertSessionHasErrors(['name', 'ip_address']);
});

it('rejects duplicate ip addresses', function (): void {
    Router::factory()->create(['ip_address' => '192.168.5.5']);

    $this->actingAs(adminUser())
        ->post(route('routers.store'), [
            'name' => 'Dup',
            'ip_address' => '192.168.5.5',
        ])
        ->assertSessionHasErrors('ip_address');
});

it('updates a router via the controller', function (): void {
    $router = Router::factory()->create(['location' => 'Old']);

    $this->actingAs(adminUser())
        ->put(route('routers.update', $router), [
            'name' => $router->name,
            'ip_address' => $router->ip_address,
            'location' => 'Updated Location',
        ])
        ->assertRedirect(route('routers.show', $router));

    expect($router->fresh()->location)->toBe('Updated Location');
});

it('deletes a router via the controller', function (): void {
    $router = Router::factory()->create();

    $this->actingAs(adminUser())
        ->delete(route('routers.destroy', $router))
        ->assertRedirect(route('routers.index'));

    expect(Router::count())->toBe(0);
});

it('returns json for the connection test endpoint', function (): void {
    $router = Router::factory()->create(['ip_address' => '192.0.2.200', 'is_active' => true]);

    $this->actingAs(adminUser())
        ->postJson(route('routers.test-connection', $router))
        ->assertOk()
        ->assertJsonStructure(['ok', 'status', 'ping_ms', 'api_reachable', 'message']);
});

it('returns json for the ping endpoint', function (): void {
    $router = Router::factory()->create(['ip_address' => '192.0.2.201']);

    $this->actingAs(adminUser())
        ->postJson(route('routers.ping', $router))
        ->assertOk()
        ->assertJsonStructure(['ok', 'ping_ms', 'message']);
});

it('blocks guests from the router module', function (): void {
    $this->get(route('routers.index'))->assertRedirect(route('login'));
});
