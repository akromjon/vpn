<?php

namespace Modules\Client\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Route;

class ClientRouteServiceProvider extends RouteServiceProvider
{
    public function boot(): void
    {

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(__DIR__ . '/../Routes/api.php');
        });
    }
}
