<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Role Middleware Tests
|--------------------------------------------------------------------------
|
| Verify that the `role`, `super_admin`, `admin`, and `operator` middleware
| aliases correctly allow or deny routes based on the authenticated user.
|
*/

beforeEach(function () {
    Route::middleware('auth')->group(function () {
        Route::get('/guard/super-admin', fn () => response()->json(['ok' => true]))
            ->middleware('super_admin')->name('guard.super-admin');
        Route::get('/guard/admin', fn () => response()->json(['ok' => true]))
            ->middleware('admin')->name('guard.admin');
        Route::get('/guard/operator', fn () => response()->json(['ok' => true]))
            ->middleware('operator')->name('guard.operator');
        Route::get('/guard/role', fn () => response()->json(['ok' => true]))
            ->middleware('role:super_admin')->name('guard.role');
    });
});

test('super admin can access the super admin route', function () {
    $this->actingAs(User::factory()->superAdmin()->create())
        ->get('/guard/super-admin')
        ->assertOk();
});

test('admin cannot access the super admin route', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->get('/guard/super-admin')
        ->assertForbidden();
});

test('operator cannot access the admin route', function () {
    $this->actingAs(User::factory()->operator()->create())
        ->get('/guard/admin')
        ->assertForbidden();
});

test('admin can access the operator route (operator includes admin)', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->get('/guard/operator')
        ->assertOk();
});

test('role:super_admin alias blocks non matching roles', function () {
    $this->actingAs(User::factory()->operator()->create())
        ->get('/guard/role')
        ->assertForbidden();
});

test('super admin can access every role-guarded route', function () {
    $this->actingAs(User::factory()->superAdmin()->create());

    $this->get('/guard/super-admin')->assertOk();
    $this->get('/guard/admin')->assertOk();
    $this->get('/guard/operator')->assertOk();
});

test('explicit role:super_admin blocks operators (exact, no hierarchy expansion)', function () {
    Route::middleware(['auth', 'role:super_admin'])->group(function () {
        Route::get('/guard/exact-super', fn () => response()->json(['ok' => true]))
            ->name('guard.exact-super');
    });

    $this->actingAs(User::factory()->operator()->create())
        ->get('/guard/exact-super')
        ->assertForbidden();

    $this->actingAs(User::factory()->admin()->create())
        ->get('/guard/exact-super')
        ->assertForbidden();

    $this->actingAs(User::factory()->superAdmin()->create())
        ->get('/guard/exact-super')
        ->assertOk();
});

test('guests are redirected to login by role middleware', function () {
    $this->get('/guard/super-admin')
        ->assertRedirect(route('login'));
});
