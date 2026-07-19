<?php

declare(strict_types=1);

use App\Models\Radius\RadiusPostAuth;
use App\Models\Radius\RadiusReply;
use App\Models\Radius\RadiusUser;
use App\Repositories\Radius\RadiusAuthRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->repo = new RadiusAuthRepository;
});

it('creates a cleartext password attribute in radcheck', function (): void {
    $row = $this->repo->setPassword('alice', 'secret123');

    expect($row)->toBeInstanceOf(RadiusUser::class)
        ->and($row->username)->toBe('alice')
        ->and($row->attribute)->toBe('Cleartext-Password')
        ->and($row->op)->toBe(':=')
        ->and($row->value)->toBe('secret123');

    expect(RadiusUser::forUsername('alice')->count())->toBe(1);
});

it('verifies correct cleartext credentials', function (): void {
    $this->repo->setPassword('bob', 'hunter2');

    expect($this->repo->verifyCredentials('bob', 'hunter2'))->toBeTrue()
        ->and($this->repo->verifyCredentials('bob', 'wrong'))->toBeFalse()
        ->and($this->repo->verifyCredentials('ghost', 'anything'))->toBeFalse();
});

it('encodes and verifies MD5 passwords', function (): void {
    $this->repo->setPassword('carol', 'p@ss', 'MD5-Password');

    $stored = RadiusUser::forUsername('carol')->first();
    expect($stored->attribute)->toBe('MD5-Password')
        ->and($stored->value)->toBe(md5('p@ss'));

    expect($this->repo->verifyCredentials('carol', 'p@ss'))->toBeTrue()
        ->and($this->repo->verifyCredentials('carol', 'nope'))->toBeFalse();
});

it('encodes and verifies SHA1 passwords', function (): void {
    $this->repo->setPassword('dave', 'letmein', 'SHA-Password');

    expect(RadiusUser::forUsername('dave')->first()->value)->toBe(sha1('letmein'));
    expect($this->repo->verifyCredentials('dave', 'letmein'))->toBeTrue();
});

it('encodes and verifies NT passwords', function (): void {
    $this->repo->setPassword('erin', 'Windows1', 'NT-Password');

    expect($this->repo->verifyCredentials('erin', 'Windows1'))->toBeTrue()
        ->and($this->repo->verifyCredentials('erin', 'windows1'))->toBeFalse();
});

it('replaces the password attribute instead of duplicating it', function (): void {
    $this->repo->setPassword('frank', 'first');
    $this->repo->setPassword('frank', 'second');

    expect(RadiusUser::forUsername('frank')->count())->toBe(1)
        ->and($this->repo->verifyCredentials('frank', 'second'))->toBeTrue()
        ->and($this->repo->verifyCredentials('frank', 'first'))->toBeFalse();
});

it('picks the highest-priority password attribute when several exist', function (): void {
    // Manually insert an MD5 row and a Cleartext row for the same user.
    RadiusUser::create(['username' => 'gina', 'attribute' => 'MD5-Password', 'op' => ':=', 'value' => md5('x')]);
    RadiusUser::create(['username' => 'gina', 'attribute' => 'Cleartext-Password', 'op' => ':=', 'value' => 'plain']);

    $row = $this->repo->passwordAttribute('gina');
    expect($row->attribute)->toBe('Cleartext-Password');
});

it('reports whether a user exists', function (): void {
    expect($this->repo->exists('helen'))->toBeFalse();
    $this->repo->setPassword('helen', 'pw');
    expect($this->repo->exists('helen'))->toBeTrue();
});

it('adds and updates arbitrary check attributes', function (): void {
    $this->repo->setCheckAttribute('ivan', 'Simultaneous-Use', '1');
    expect(RadiusUser::forUsername('ivan')->where('attribute', 'Simultaneous-Use')->first()->value)->toBe('1');

    $this->repo->setCheckAttribute('ivan', 'Simultaneous-Use', '2');
    expect(RadiusUser::forUsername('ivan')->where('attribute', 'Simultaneous-Use')->count())->toBe(1)
        ->and(RadiusUser::forUsername('ivan')->where('attribute', 'Simultaneous-Use')->first()->value)->toBe('2');
});

it('deletes a user across radcheck, radreply and radusergroup', function (): void {
    $this->repo->setPassword('jane', 'pw');
    RadiusReply::create(['username' => 'jane', 'attribute' => 'Framed-IP-Address', 'op' => '=', 'value' => '10.0.0.9']);

    $deleted = $this->repo->deleteUser('jane');

    expect($deleted)->toBeGreaterThanOrEqual(2)
        ->and(RadiusUser::forUsername('jane')->count())->toBe(0)
        ->and(RadiusReply::forUsername('jane')->count())->toBe(0);
});

it('records a post-auth attempt', function (): void {
    $row = $this->repo->recordPostAuth('kyle', RadiusPostAuth::REPLY_ACCEPT, null, '00:11:22:33:44:55');

    expect($row->username)->toBe('kyle')
        ->and($row->reply)->toBe('Access-Accept')
        ->and($row->callingstationid)->toBe('00:11:22:33:44:55')
        ->and($row->authdate)->not->toBeNull();
});
