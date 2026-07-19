<?php

declare(strict_types=1);

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Radius\RouterController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

// ---------------------------------------------------------------------------
// Role-specific dashboards (each guarded by its own role middleware).
// ---------------------------------------------------------------------------
Route::middleware(['auth', 'super_admin'])->group(function (): void {
    Route::get('/super-admin/dashboard', [DashboardController::class, 'superAdmin'])
        ->name('dashboard.super-admin');
});

Route::middleware(['auth', 'admin'])->group(function (): void {
    Route::get('/admin/dashboard', [DashboardController::class, 'admin'])
        ->name('dashboard.admin');
});

Route::middleware(['auth', 'operator'])->group(function (): void {
    Route::get('/operator/dashboard', [DashboardController::class, 'operator'])
        ->name('dashboard.operator');
});

// Generic dashboard redirect based on role (kept for compatibility).
Route::get('/dashboard', function () {
    return redirect()->route(match (true) {
        auth()->user()?->isSuperAdmin() => 'dashboard.super-admin',
        auth()->user()?->isAdmin() => 'dashboard.admin',
        default => 'dashboard.operator',
    });
})->middleware(['auth', 'verified'])->name('dashboard');

// Example route demonstrating the Service / Repository layering.
Route::middleware(['auth', 'admin'])->group(function (): void {
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
});

// ---------------------------------------------------------------------------
// Router (NAS) module — CRUD + reachability tests. Admin+ only.
// ---------------------------------------------------------------------------
Route::middleware(['auth', 'admin'])->group(function (): void {
    Route::resource('routers', RouterController::class);
    Route::post('/routers/{router}/ping', [RouterController::class, 'ping'])
        ->name('routers.ping');
    Route::post('/routers/{router}/test-connection', [RouterController::class, 'testConnection'])
        ->name('routers.test-connection');
});

Route::middleware('auth')->group(function (): void {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
