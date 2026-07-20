<?php

declare(strict_types=1);

use App\Models\Billing\Package;
use App\Repositories\Billing\PackageRepository;
use App\Services\PackageService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->service = app(PackageService::class);
});

it('resolves PackageService from the container', function (): void {
    expect($this->service)->toBeInstanceOf(PackageService::class);
});

it('creates a package', function (): void {
    $package = $this->service->create([
        'name' => 'Home 20Mbps',
        'duration_days' => 30,
        'speed_download' => '20M',
        'speed_upload' => '20M',
        'price' => 150000,
        'radius_profile' => 'profile-20m',
        'description' => 'Home fiber plan',
    ]);

    expect($package)->toBeInstanceOf(Package::class)
        ->and($package->id)->toBeString()
        ->and($package->name)->toBe('Home 20Mbps')
        ->and($package->radius_profile)->toBe('profile-20m')
        ->and($package->is_active)->toBeTrue()
        ->and($package->code)->not->toBeEmpty();
});

it('auto-generates a unique code when none is given', function (): void {
    $a = $this->service->create(['name' => 'Plan A', 'duration_days' => 30, 'price' => 1]);
    $b = $this->service->create(['name' => 'Plan A', 'duration_days' => 30, 'price' => 1]);

    expect($a->code)->not->toBe($b->code);
});

it('respects a supplied code', function (): void {
    $package = $this->service->create([
        'name' => 'Custom', 'duration_days' => 30, 'price' => 1, 'code' => 'MYCODE',
    ]);

    expect($package->code)->toBe('MYCODE');
});

it('lists packages ordered by name', function (): void {
    Package::factory()->create(['name' => 'Zeta']);
    Package::factory()->create(['name' => 'Alpha']);

    expect($this->service->list()->pluck('name')->all())->toBe(['Alpha', 'Zeta']);
});

it('lists only active packages ordered by price', function (): void {
    Package::factory()->create(['is_active' => true, 'price' => 200]);
    Package::factory()->create(['is_active' => true, 'price' => 100]);
    Package::factory()->inactive()->create(['price' => 50]);

    $active = $this->service->listActive();
    expect($active)->toHaveCount(2)
        ->and($active->pluck('price')->map(fn ($p) => (int) $p)->all())->toBe([100, 200]);
});

it('updates a package', function (): void {
    $package = Package::factory()->create(['price' => 100000]);

    $updated = $this->service->update($package->id, ['price' => 250000, 'radius_profile' => 'vip']);

    expect((int) $updated->price)->toBe(250000)
        ->and($updated->radius_profile)->toBe('vip');
});

it('soft-deletes a package', function (): void {
    $package = Package::factory()->create();

    expect($this->service->delete($package->id))->toBeTrue();
    expect(Package::count())->toBe(0)
        ->and(Package::withTrashed()->count())->toBe(1);
});

it('supports an unlimited number of packages', function (): void {
    Package::factory()->count(30)->create();
    expect($this->service->list())->toHaveCount(30);
});

it('builds a mikrotik-style rate limit string', function (): void {
    $package = Package::factory()->make(['speed_upload' => '5M', 'speed_download' => '20M']);
    expect($package->rateLimit())->toBe('5M/20M');
});

it('finds a package by code via the repository', function (): void {
    Package::factory()->create(['code' => 'FINDME']);

    $repo = app(PackageRepository::class);
    expect($repo->findByCode('FINDME'))->not->toBeNull()
        ->and($repo->findByCode('NOPE'))->toBeNull();
});
