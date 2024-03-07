<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Bank\EloquentBankRepository;
use App\Repositories\User\EloquentUserRepository;
use App\Repositories\Bank\BankRepositoryInterface;
use App\Repositories\User\UserRepositoryInterface;
use App\Repositories\Store\EloquentStoreRepository;
use App\Repositories\Token\EloquentTokenRepository;
use App\Repositories\Store\StoreRepositoryInterface;
use App\Repositories\Token\TokenRepositoryInterface;
use App\Repositories\Contact\EloquentContactRepository;
use App\Repositories\Contact\ContactRepositoryInterface;
use App\Repositories\Inventory\EloquentInventoryRepository;
use App\Repositories\Warehouse\EloquentWarehouseRepository;
use App\Repositories\Inventory\InventoryRepositoryInterface;
use App\Repositories\Warehouse\WarehouseRepositoryInterface;
use App\Repositories\Convection\EloquentConvectionRepository;
use App\Repositories\Inventory\Stock\EloquentStockRepository;
use App\Repositories\Convection\ConvectionRepositoryInterface;
use App\Repositories\Inventory\Stock\StockRepositoryInterface;
use App\Repositories\PurchaseOrder\EloquentPurchaseOrderRepository;
use App\Repositories\PurchaseOrder\PurchaseOrderRepositoryInterface;
use App\Repositories\Inventory\Transfer\In\EloquentTransferInRepository;
use App\Repositories\Inventory\Transfer\In\TransferInRepositoryInterface;


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
        $this->app->bind(TokenRepositoryInterface::class, EloquentTokenRepository::class);
        $this->app->bind(BankRepositoryInterface::class, EloquentBankRepository::class);
        $this->app->bind(ContactRepositoryInterface::class, EloquentContactRepository::class);
        $this->app->bind(PurchaseOrderRepositoryInterface::class, EloquentPurchaseOrderRepository::class);
        $this->app->bind(StockRepositoryInterface::class, EloquentStockRepository::class);
        $this->app->bind(TransferInRepositoryInterface::class, EloquentTransferInRepository::class);
        // $this->app->bind(PurchaseOrderRepositoryInterface::class, EloquentPurchaseOrderRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}