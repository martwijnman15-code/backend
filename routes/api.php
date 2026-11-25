<?php

use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\LoginController;
use App\Http\Controllers\Api\LogoutController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::post('v1/login', LoginController::class)->middleware('throttle:5,1')->name('api.login');

Route::prefix('v1')->middleware(['auth:sanctum', 'throttle:100,1'])->group(function () {
    Route::post('logout', LogoutController::class)->name('api.logout');
    Route::apiResource('projects', ProjectController::class);
    Route::apiResource('customers', CustomerController::class);
    Route::apiResource('users', UserController::class);
});
