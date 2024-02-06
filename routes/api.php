<?php

use App\Http\Controllers\SettingsController;
use App\Http\Controllers\WebsiteController;
use Illuminate\Support\Facades\Route;
use Modules\Client\Http\Controllers\TokenController;
use Modules\Server\Http\Controllers\ServerController;



Route::middleware(['token','version'])->prefix('/settings')->group(function () {

    Route::get("/version", [SettingsController::class, 'getVersion']);

});

Route::post("/contact",[WebsiteController::class,'contact']);
