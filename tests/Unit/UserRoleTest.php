<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\User;

test('user role is cast to the UserRole enum', function () {
    $user = User::factory()->admin()->create();

    expect($user->role)->toBeInstanceOf(UserRole::class)
        ->and($user->role)->toBe(UserRole::ADMIN);
});

test('role helper methods behave correctly', function () {
    $super = User::factory()->superAdmin()->create();
    $admin = User::factory()->admin()->create();
    $operator = User::factory()->operator()->create();

    expect($super->isSuperAdmin())->toBeTrue()
        ->and($super->isAdmin())->toBeTrue()
        ->and($admin->isAdmin())->toBeTrue()
        ->and($admin->isSuperAdmin())->toBeFalse()
        ->and($operator->isOperator())->toBeTrue()
        ->and($operator->isAdmin())->toBeFalse();
});

test('user role defaults to operator when omitted', function () {
    $user = User::factory()->create();

    expect($user->role)->toBe(UserRole::OPERATOR);
});

test('user role values match the expected set', function () {
    expect(UserRole::values())->toBe([
        'super_admin',
        'admin',
        'operator',
    ]);
});
