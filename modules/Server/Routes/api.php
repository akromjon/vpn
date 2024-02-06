<?php

use Modules\Server\Http\Controllers\ServerController;
use Illuminate\Support\Facades\Route;

Route::middleware(['token','version'])->prefix("/servers")->group(function () {

    Route::get("/", [ServerController::class, 'list']);

    Route::post("/{ip}/download", [ServerController::class, 'download']);
});
