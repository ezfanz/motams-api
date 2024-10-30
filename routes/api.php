<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\User\UserController;

Route::group(['prefix' => 'v1', 'middleware' => 'api'], function () {

    /**
     * Auth
     */
    Route::group(['prefix' => 'auth'], function () {
        Route::post('/register', [AuthController::class, 'register'])->name('auth.register');
        Route::post('/login', [AuthController::class, 'login'])->name('auth.login');
        Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api')->name('auth.logout');
        Route::post('/refresh', [AuthController::class, 'refresh'])->middleware('auth:api')->name('auth.refresh');
        Route::post('/profile', [AuthController::class, 'profile'])->middleware('auth:api')->name('auth.profile');
    });

    /**
     * User Profile
     */
    Route::group(['middleware' => 'auth:api'], function () {
        Route::apiResource('users', UserController::class);
    });
});
