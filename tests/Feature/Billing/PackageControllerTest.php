<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\Billing\Package;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function packageAdmin(): User
{
    return User::factory()->create(['role' => UserRole::ADMIN]);
}

it('lists packages on the index page', function (): void {
    Package::factory()->create(['name' => 'Fiber-100']);

    $this->actingAs(packageAdmin())
        ->get(route('packages.index'))
        ->assertOk()
        ->assertSee('Fiber-100');
});

it('shows the create form', function (): void {
    $this->actingAs(packageAdmin())
        ->get(route('packages.create'))
        ->assertOk()
        ->assertSee('Radius Profile');
});

it('creates a package via the controller', function (): void {
    $response = $this->actingAs(packageAdmin())->post(route('packages.store'), [
        'name' => 'Business 50Mbps',
        'duration_days' => 30,
        'speed_download' => '50M',
        'speed_upload' => '50M',
        'price' => 500000,
        'radius_profile' => 'profile-50m',
        'description' => 'Business plan',
        'is_active' => '1',
    ]);

    $package = Package::where('name', 'Business 50Mbps')->first();
    expect($package)->not->toBeNull()
        ->and($package->radius_profile)->toBe('profile-50m');
    $response->assertRedirect(route('packages.show', $package));
});

it('validates required fields', function (): void {
    $this->actingAs(packageAdmin())
        ->post(route('packages.store'), ['name' => '', 'duration_days' => '', 'price' => ''])
        ->assertSessionHasErrors(['name', 'duration_days', 'price']);
});

it('rejects a non-numeric price', function (): void {
    $this->actingAs(packageAdmin())
        ->post(route('packages.store'), [
            'name' => 'Bad', 'duration_days' => 30, 'price' => 'free',
        ])
        ->assertSessionHasErrors('price');
});

it('rejects duplicate codes', function (): void {
    Package::factory()->create(['code' => 'DUP123']);

    $this->actingAs(packageAdmin())
        ->post(route('packages.store'), [
            'name' => 'Dup', 'duration_days' => 30, 'price' => 1, 'code' => 'DUP123',
        ])
        ->assertSessionHasErrors('code');
});

it('updates a package via the controller', function (): void {
    $package = Package::factory()->create(['price' => 100000]);

    $this->actingAs(packageAdmin())
        ->put(route('packages.update', $package), [
            'name' => $package->name,
            'duration_days' => $package->duration_days,
            'price' => 300000,
            'radius_profile' => 'updated-profile',
        ])
        ->assertRedirect(route('packages.show', $package));

    expect((int) $package->fresh()->price)->toBe(300000)
        ->and($package->fresh()->radius_profile)->toBe('updated-profile');
});

it('deletes a package via the controller', function (): void {
    $package = Package::factory()->create();

    $this->actingAs(packageAdmin())
        ->delete(route('packages.destroy', $package))
        ->assertRedirect(route('packages.index'));

    expect(Package::count())->toBe(0);
});

it('blocks guests from the package module', function (): void {
    $this->get(route('packages.index'))->assertRedirect(route('login'));
});
