<?php

use App\Http\Controllers\V1\User\Authentication\AuthenticationController;
use App\Http\Controllers\V1\User\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::group([
    'as' => 'api.',
], function () {

    Route::group([
        'prefix' => 'v1',
        'as' => 'v1.',
    ], function () {

        Route::post('login/microsoft', [AuthenticationController::class, 'loginByMicrosoft'])->name('login-microsoft');
        Route::post('login', [AuthenticationController::class, 'login'])->name('login');
        Route::post('register', [AuthenticationController::class, 'register'])->name('register');

        Route::group([
            'middleware' => 'auth:sanctum',
        ], function () {

            Route::get('/user', [UserController::class, 'user'])->name('user');

        });

    });
});
