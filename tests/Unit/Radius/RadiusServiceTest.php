<?php

declare(strict_types=1);

use App\Models\Radius\RadiusAccounting;
use App\Models\Radius\RadiusGroup;
use App\Models\Radius\RadiusPostAuth;
use App\Models\Radius\RadiusReply;
use App\Models\Radius\RadiusUser;
use App\Services\RadiusService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->service = app(RadiusService::class);
});

it('resolves RadiusService from the container with a bound repository', function (): void {
    expect($this->service)->toBeInstanceOf(RadiusService::class);
});

it('provisions a user with password, group and reply attributes', function (): void {
    $this->service->createUser('nina', 'secret', 'premium', [
        'Framed-IP-Address' => '10.0.0.20',
        'Mikrotik-Rate-Limit' => '10M/10M',
    ]);

    expect(RadiusUser::forUsername('nina')->count())->toBe(1)
        ->and(RadiusGroup::forUsername('nina')->forGroup('premium')->exists())->toBeTrue()
        ->and(RadiusReply::forUsername('nina')->count())->toBe(2)
        ->and(RadiusReply::forUsername('nina')->attribute('Framed-IP-Address')->first()->value)->toBe('10.0.0.20');
});

it('authenticates valid credentials and logs an accept', function (): void {
    $this->service->createUser('omar', 'topsecret');

    expect($this->service->authenticate('omar', 'topsecret', 'AA:BB:CC'))->toBeTrue();

    $post = RadiusPostAuth::forUsername('omar')->first();
    expect($post->reply)->toBe(RadiusPostAuth::REPLY_ACCEPT)
        ->and($post->callingstationid)->toBe('AA:BB:CC');
});

it('rejects invalid credentials and logs a reject', function (): void {
    $this->service->createUser('paul', 'rightpw');

    expect($this->service->authenticate('paul', 'wrongpw'))->toBeFalse();

    expect(RadiusPostAuth::forUsername('paul')->first()->reply)->toBe(RadiusPostAuth::REPLY_REJECT);
});

it('updates a password', function (): void {
    $this->service->createUser('quinn', 'oldpw');
    $this->service->updatePassword('quinn', 'newpw');

    expect($this->service->authenticate('quinn', 'newpw'))->toBeTrue()
        ->and($this->service->authenticate('quinn', 'oldpw'))->toBeFalse();
});

it('assigns and removes groups idempotently', function (): void {
    $this->service->assignGroup('rita', 'basic', 1);
    $this->service->assignGroup('rita', 'basic', 5); // update, not duplicate

    expect(RadiusGroup::forUsername('rita')->forGroup('basic')->count())->toBe(1)
        ->and(RadiusGroup::forUsername('rita')->forGroup('basic')->first()->priority)->toBe(5);

    $removed = $this->service->removeFromGroup('rita', 'basic');
    expect($removed)->toBe(1)
        ->and(RadiusGroup::forUsername('rita')->count())->toBe(0);
});

it('reports user existence and deletes users', function (): void {
    $this->service->createUser('sam', 'pw', 'g1');
    expect($this->service->userExists('sam'))->toBeTrue();

    $this->service->deleteUser('sam');
    expect($this->service->userExists('sam'))->toBeFalse()
        ->and(RadiusGroup::forUsername('sam')->count())->toBe(0);
});

it('lists active sessions and computes total usage', function (): void {
    RadiusAccounting::create([
        'acctsessionid' => 's1', 'acctuniqueid' => 'u1', 'username' => 'tom',
        'nasipaddress' => '192.168.1.1', 'acctstarttime' => now()->subHour(),
        'acctinputoctets' => 1000, 'acctoutputoctets' => 2000, 'acctstoptime' => now(),
    ]);
    RadiusAccounting::create([
        'acctsessionid' => 's2', 'acctuniqueid' => 'u2', 'username' => 'tom',
        'nasipaddress' => '192.168.1.1', 'acctstarttime' => now()->subMinutes(10),
        'acctinputoctets' => 500, 'acctoutputoctets' => 500, 'acctstoptime' => null,
    ]);

    expect($this->service->activeSessions('tom'))->toHaveCount(1)
        ->and($this->service->totalUsage('tom'))->toBe(4000);
});

it('returns auth history most-recent-first', function (): void {
    $this->service->createUser('uma', 'pw');
    $this->service->authenticate('uma', 'wrong');
    $this->service->authenticate('uma', 'pw');

    $history = $this->service->authHistory('uma');
    expect($history)->toHaveCount(2)
        ->and($history->first()->reply)->toBe(RadiusPostAuth::REPLY_ACCEPT);
});

it('computes total octets on an accounting row', function (): void {
    $row = new RadiusAccounting(['acctinputoctets' => 100, 'acctoutputoctets' => 250]);
    expect($row->totalOctets())->toBe(350);
});
