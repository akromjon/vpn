<?php

use Illuminate\Support\Facades\Route;
use Akromjon\Pritunl\Pritunl;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
        $pritunl = new Pritunl('143.110.242.252', "akrom", "akromjon98");

        $organizationId = "6567162cb8915c9dd36bd0c9";

        $userId = "65671630b8915c9dd36bd0d8";

       return $pritunl->download($organizationId,$userId);
});
