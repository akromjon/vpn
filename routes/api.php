<?php

use App\Http\Controllers\ClientController;
use App\Http\Controllers\ServerController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TokenController;
use Illuminate\Support\Facades\Route;



Route::middleware(['api-key','version'])->prefix('/token')->group(function () {

    Route::post("/", [TokenController::class, 'generateToken']);

});

Route::middleware(['token','version'])->prefix("/servers")->group(function () {

    Route::get("/", [ServerController::class, 'list']);

    Route::post("/{ip}/download", [ServerController::class, 'download']);

    Route::get("/connected", [ServerController::class, 'connected']);

    Route::get("/disconnected", [ServerController::class, 'disconnected']);

});

Route::middleware(['token','version'])->prefix('/client')->group(function () {

    Route::get("/status", [ClientController::class, 'status']);
    Route::get("/getMe", [ClientController::class, 'getMe']);
});

Route::middleware(['token'])->prefix('/settings')->group(function () {

    Route::get("/version", [SettingsController::class, 'getVersion']);

});

Route::middleware('pritunl-user-action')->get("/pritunl-user-action/{action}/{uuid}", [ServerController::class, 'pritunlUserAction']);
