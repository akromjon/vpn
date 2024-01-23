<?php

namespace Modules\Server\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Route;

class ServerRouteServiceProvider extends RouteServiceProvider
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
