<?php

use App\Http\Controllers\ServerController;
use App\Http\Controllers\TokenController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('api-key')->prefix('/token')->group(function () {

    Route::post("/",[TokenController::class, 'generateToken']);

});

Route::middleware('token')->prefix("/servers")->group(function () {

    Route::get("/",[ServerController::class, 'list']);

    Route::get("/{ip}/connect",[ServerController::class, 'connect']);

});
