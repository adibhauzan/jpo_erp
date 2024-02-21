<?php

namespace App\Providers;

use App\Repositories\Convection\ConvectionRepositoryInterface;
use App\Repositories\Convection\EloquentConvectionRepository;
use App\Repositories\Store\EloquentStoreRepository;
use App\Repositories\Store\StoreRepositoryInterface;
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
        $this->app->bind(StoreRepositoryInterface::class, EloquentStoreRepository::class);
        $this->app->bind(ConvectionRepositoryInterface::class, EloquentConvectionRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}