<?php

declare(strict_types=1);

use App\Models\Radius\RadiusGroupReply;
use App\Services\RadiusProfileService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->service = app(RadiusProfileService::class);
});

it('lists distinct profiles', function (): void {
    RadiusGroupReply::create(['groupname' => 'voucher', 'attribute' => 'Session-Timeout', 'value' => '28800']);
    RadiusGroupReply::create(['groupname' => 'voucher', 'attribute' => 'Mikrotik-Rate-Limit', 'value' => '2M/4M']);
    RadiusGroupReply::create(['groupname' => 'profile-20m', 'attribute' => 'Mikrotik-Rate-Limit', 'value' => '20M/20M']);

    $groups = $this->service->list()->all();
    expect($groups)->toBe(['profile-20m', 'voucher']);
});

it('returns attributes for a profile as a map', function (): void {
    RadiusGroupReply::create(['groupname' => 'p', 'attribute' => 'A', 'value' => '1']);
    RadiusGroupReply::create(['groupname' => 'p', 'attribute' => 'B', 'value' => '2']);

    expect($this->service->attributesMap('p'))->toBe(['A' => '1', 'B' => '2']);
});

it('saves (creates) a profile, replacing existing attributes', function (): void {
    $this->service->saveProfile('profile-10m', [
        ['attribute' => 'Mikrotik-Rate-Limit', 'op' => ':=', 'value' => '10M/10M'],
        ['attribute' => 'Session-Timeout', 'op' => ':=', 'value' => '86400'],
    ]);

    $attrs = $this->service->attributesMap('profile-10m');
    expect($attrs)->toHaveKey('Mikrotik-Rate-Limit', '10M/10M')
        ->and($attrs)->toHaveKey('Session-Timeout', '86400');

    // re-save with one less attribute -> old ones removed (replace semantics)
    $this->service->saveProfile('profile-10m', [
        ['attribute' => 'Mikrotik-Rate-Limit', 'op' => ':=', 'value' => '20M/20M'],
    ]);

    expect(RadiusGroupReply::forGroup('profile-10m')->count())->toBe(1)
        ->and($this->service->attributesMap('profile-10m'))->toBe(['Mikrotik-Rate-Limit' => '20M/20M']);
});

it('ignores empty attribute rows when saving', function (): void {
    $this->service->saveProfile('p', [
        ['attribute' => '', 'value' => ''],
        ['attribute' => 'Mikrotik-Rate-Limit', 'value' => '5M/5M'],
    ]);

    expect(RadiusGroupReply::forGroup('p')->count())->toBe(1);
});

it('deletes a profile', function (): void {
    RadiusGroupReply::create(['groupname' => 'del', 'attribute' => 'A', 'value' => '1']);
    $this->service->deleteProfile('del');
    expect(RadiusGroupReply::forGroup('del')->count())->toBe(0);
});

it('groups all profiles with attributes', function (): void {
    RadiusGroupReply::create(['groupname' => 'x', 'attribute' => 'A', 'value' => '1']);
    $all = $this->service->allWithAttributes();
    expect($all)->toHaveKey('x')
        ->and($all['x'])->toHaveCount(1);
});
