<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Bot\TelegramBotController;



Route::post('/telegram/webhook', [TelegramBotController::class, 'webhook']);
Route::post('/login', [AuthController::class, 'login']);


Route::middleware(['auth:sanctum'])->group(function () {

    Route::middleware(['role:admin,superadmin'])->group(function () {
        Route::apiResource('users', UserController::class);
    });
    Route::middleware(['role:superadmin'])->group(function () {
        Route::apiResource('departments', DepartmentController::class);
    });
});
