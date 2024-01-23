<?php

use Modules\Client\Http\Controllers\ClientController;
use Illuminate\Support\Facades\Route;

Route::middleware(['token','version'])->prefix('/client')->group(function () {

    Route::delete("/delete", [ClientController::class, 'delete']);

    Route::get("/status", [ClientController::class, 'status']);

    Route::get("/getMe", [ClientController::class, 'getMe']);

});
