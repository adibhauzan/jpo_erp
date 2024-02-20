<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;

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


        Route::middleware(['role:superadmin'])->group(function () {
            Route::get('user', [UserController::class, 'index']);
            Route::get('user', [UserController::class, 'findByParameters']);
            Route::post('register', [AuthController::class, 'register']);
            Route::post('user/ban/{id}', [UserController::class, 'banUser']);
            Route::post('user/update/{id}', [UserController::class, 'update']);
        });

        Route::middleware(['role:store'])->group(function () {
        });

        Route::middleware(['role:convection'])->group(function () {
        });
    });
});