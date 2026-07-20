<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\User;
use App\Repositories\Radius\RadiusAuthRepository;
use App\Repositories\Radius\RadiusAuthRepositoryInterface;
use App\Repositories\RepositoryInterface;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind the User repository interface to its concrete implementation so
        // services can depend on the contract and stay persistence-agnostic.
        $this->app->bind(RepositoryInterface::class, UserRepository::class);

        // FreeRADIUS authentication repository (PostgreSQL implementation).
        $this->app->bind(RadiusAuthRepositoryInterface::class, RadiusAuthRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('super_admin', static function ($user): bool {
            return $user instanceof User && $user->isSuperAdmin();
        });
    }
}
