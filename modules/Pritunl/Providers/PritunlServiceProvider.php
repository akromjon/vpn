<?php

namespace Modules\Pritunl\Providers;

use Illuminate\Support\ServiceProvider;

class PritunlServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
    }
}
