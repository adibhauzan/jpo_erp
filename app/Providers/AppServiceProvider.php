<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\User\EloquentUserRepository;
use App\Repositories\User\UserRepositoryInterface;
use App\Repositories\Store\EloquentStoreRepository;
use App\Repositories\Store\StoreRepositoryInterface;
use App\Repositories\Warehouse\EloquentWarehouseRepository;
use App\Repositories\Warehouse\WarehouseRepositoryInterface;
use App\Repositories\Convection\EloquentConvectionRepository;
use App\Repositories\Convection\ConvectionRepositoryInterface;


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
        $this->app->bind(WarehouseRepositoryInterface::class, EloquentWarehouseRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}