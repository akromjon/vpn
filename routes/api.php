<?php

use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TokenController;
use App\Http\Controllers\WebsiteController;
use Illuminate\Support\Facades\Route;
use Modules\Server\Http\Controllers\ServerController;

Route::middleware(['api-key'])->prefix('/token')->group(function () {

    Route::post("/", [TokenController::class, 'generateToken']);

});

Route::middleware(['token','version'])->prefix('/settings')->group(function () {

    Route::get("/version", [SettingsController::class, 'getVersion']);

});

Route::middleware('pritunl-user-action')->get("/pritunl-user-action/{action}/{uuid}", [ServerController::class, 'pritunlUserAction']);


Route::post("/contact",[WebsiteController::class,'contact']);
