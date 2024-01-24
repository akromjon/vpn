<?php

use Modules\Client\Http\Controllers\ClientController;
use Illuminate\Support\Facades\Route;
use Modules\Client\Http\Controllers\TokenController;


Route::middleware(['api-key'])->post("/token", [TokenController::class, 'generateToken']);


Route::middleware(['token', 'version'])->prefix('/client')->group(function () {

    Route::delete("/delete", [ClientController::class, 'delete']);

    Route::get("/status", [ClientController::class, 'status']);

    Route::get("/getMe", [ClientController::class, 'getMe']);

});
