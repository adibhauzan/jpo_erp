<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\StoreController;
use App\Http\Controllers\Api\ConvectionController;

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

    // Routes for authentication
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
                Route::post('update/{id}', [UserController::class, 'update']);
            });
            Route::get('stores', [StoreController::class, 'index']);
            Route::group(['prefix' => 'store'], function () {
                Route::post('/', [StoreController::class, 'store'])->name('create_store');
                Route::get('/{id}', [StoreController::class, 'show']);
                Route::put('u/{id}', [StoreController::class, 'update']);
                Route::delete('d/{id}', [StoreController::class, 'delete']);
                // Route::post('ban/{id}', [StoreController::class, 'ban']);
                // Route::post('unban/{id}', [StoreController::class, 'unBan']);
            });

            Route::get('convections', [ConvectionController::class, 'index']);
            Route::group(['prefix' => 'convection'], function () {
                Route::post('/', [ConvectionController::class, 'convection'])->name('create_convection');
                Route::get('/{id}', [ConvectionController::class, 'show']);
                Route::put('u/{id}', [ConvectionController::class, 'update']);
                Route::delete('d/{id}', [ConvectionController::class, 'delete']);
                // Route::post('ban/{id}', [ConvectionController::class, 'ban']);
                // Route::post('unban/{id}', [ConvectionController::class, 'unBan']);
            });
        });

        Route::middleware(['role:store'])->group(function () {
        });

        Route::middleware(['role:convection'])->group(function () {
        });
    });
});