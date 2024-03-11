<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BankControler;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\StoreController;
use App\Http\Controllers\Api\TokenController;
use App\Http\Controllers\Api\BrokerController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\PurchaseController;
use App\Http\Controllers\Api\WarehouseController;
use App\Http\Controllers\Api\ConvectionController;
use App\Http\Controllers\Api\Inventory\Stock\StockController;
use App\Http\Controllers\Api\Inventory\Transfer\TransferInController;
use App\Http\Controllers\Api\SalesOrderController;
use App\Models\SalesOrder;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/



Route::group(['middleware' => 'api', 'prefix' => 'auth'], function ($router) {

    Route::middleware(['auth.banned'])->group(function () {
        Route::post('login', [AuthController::class, 'login']);
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('refresh', [AuthController::class, 'refresh']);
        Route::post('me', [AuthController::class, 'me']);


        // Routes accessible only by superadmin role
        Route::middleware(['role:superadmin'])->group(function () {
            Route::post('register', [AuthController::class, 'register']);
            Route::get('users', [UserController::class, 'index']);
            Route::group(['prefix' => 'user'], function () {
                Route::get('/', [UserController::class, 'findByParameters']);
                Route::get('/{id}', [UserController::class, 'find']);
                Route::post('ban/{id}', [UserController::class, 'banUser']);
                Route::post('unban/{id}', [UserController::class, 'unBanUser']);
                Route::put('update/{id}', [UserController::class, 'update']);
                Route::delete('d/{id}', [UserController::class, 'delete']);
            });
            Route::get('stores', [StoreController::class, 'index']);
            Route::group(['prefix' => 'store'], function () {
                Route::post('/', [StoreController::class, 'store'])->name('create_store');
                Route::get('/{id}', [StoreController::class, 'show']);
                Route::put('u/{id}', [StoreController::class, 'update']);
                Route::delete('d/{id}', [StoreController::class, 'delete']);
                Route::post('ban/{id}', [StoreController::class, 'banStore']);
                Route::post('unban/{id}', [StoreController::class, 'unBanStore']);
            });

            Route::get('convections', [ConvectionController::class, 'index']);
            Route::group(['prefix' => 'convection'], function () {
                Route::post('/', [ConvectionController::class, 'store'])->name('create_convection');
                Route::get('/{id}', [ConvectionController::class, 'show']);
                Route::put('u/{id}', [ConvectionController::class, 'update']);
                Route::delete('d/{id}', [ConvectionController::class, 'delete']);
                Route::post('ban/{id}', [ConvectionController::class, 'banConvection']);
                Route::post('unban/{id}', [ConvectionController::class, 'unBanConvection']);
            });
            Route::get('warehouses', [WarehouseController::class, 'index']);
            Route::group(['prefix' => 'warehouse'], function () {
                Route::post('/', [WarehouseController::class, 'store'])->name('create_warehouse');
                Route::get('/{id}', [WarehouseController::class, 'show']);
                Route::put('u/{id}', [WarehouseController::class, 'update']);
                Route::delete('d/{id}', [WarehouseController::class, 'delete']);
                Route::post('ban/{id}', [WarehouseController::class, 'banWarehouse']);
                Route::post('unban/{id}', [WarehouseController::class, 'unBanWarehouse']);
            });
            // Token
            Route::get('tokens', [TokenController::class, 'index']);
            Route::group(['prefix' => 'token'], function () {
                Route::post('c/{jumlah}', [TokenController::class, 'store']);
                Route::get('/{id}', [TokenController::class, 'show']);
                Route::put('u/{id}', [TokenController::class, 'update']);
                Route::delete('d/{id}', [TokenController::class, 'delete']);
            });

            // Bank
            Route::get('banks', [BankControler::class, 'index']);
            Route::group(['prefix' => 'bank'], function () {
                Route::post('/', [BankControler::class, 'store']);
                Route::get('/{id}', [BankControler::class, 'show']);
                Route::put('u/{id}', [BankControler::class, 'update']);
                Route::delete('d/{id}', [BankControler::class, 'delete']);
            });
        });


        Route::middleware(['role:store'])->group(function () {
            Route::get('warehouses-list ', [WarehouseController::class, 'getWarehousesByLoggedUser']);
            Route::get('warehouse/{id}', [WarehouseController::class, 'show']);
            Route::get('contacts', [ContactController::class, 'index']);
            Route::group(['prefix' => 'contact'], function () {
                Route::post('/', [ContactController::class, 'store']);
                Route::get('/{id}', [ContactController::class, 'show']);
                Route::put('u/{id}', [ContactController::class, 'update']);
                Route::delete('d/{id}', [ContactController::class, 'delete']);
            });

            Route::get('purchase-orders', [PurchaseController::class, 'index']);
            Route::group(['prefix' => 'purchase-order'], function () {
                Route::post('/', [PurchaseController::class, 'store']);
                Route::get('/{id}', [PurchaseController::class, 'show']);
                Route::put('u/{id}', [PurchaseController::class, 'update']);
                Route::delete('d/{id}', [PurchaseController::class, 'delete']);
            });

            Route::group(['prefix' => 'inventory'], function () {
                Route::get('stocks', [StockController::class, 'index']);
                Route::group(['prefix' => 'stock'], function () {
                    Route::post('/', [StockController::class, 'store']);
                    Route::get('/{id}', [StockController::class, 'show']);
                    Route::put('u/{id}', [StockController::class, 'update']);
                    Route::delete('d/{id}', [StockController::class, 'delete']);
                });
                Route::group(['prefix' => 'transfer-in'], function () {
                    Route::post('/', [TransferInController::class, 'store']);
                    Route::get('/i/', [TransferInController::class, 'index']);
                    Route::get('/{id}', [TransferInController::class, 'show']);
                    Route::put('/{id}/receive', [TransferInController::class, 'receive']);
                    Route::put('u/{id}', [TransferInController::class, 'update']);
                    Route::delete('d/{id}', [TransferInController::class, 'delete']);
                });
            });

            Route::get('sales-orders', [SalesOrderController::class, 'index']);
            Route::group(['prefix' => 'sales-order'], function () {
                Route::post('/', [SalesOrderController::class, 'store']);
                Route::get('/{id}', [SalesOrderController::class, 'show']);
                Route::get('/{sku}/sku', [SalesOrderController::class, 'getSku']);
                Route::put('u/{id}', [SalesOrderController::class, 'update']);
                // Route::delete('d/{id}', [PurchaseController::class, 'delete']);
            });
        });
    });
});
