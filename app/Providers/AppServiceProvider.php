<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\User\EloquentUserRepository;
use App\Repositories\User\UserRepositoryInterface;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}