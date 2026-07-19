<?php

declare(strict_types=1);

namespace App\Providers;

use App\Repositories\RepositoryInterface;
use App\Repositories\UserRepository;
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
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
