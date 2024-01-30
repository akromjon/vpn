<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Request;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        if (config("app.dns_provider") === "cloudflare") {

            Request::setTrustedProxies(
                ['REMOTE_ADDR'],
                Request::HEADER_X_FORWARDED_FOR
            );
        }

        Model::unguard();
    }
}
