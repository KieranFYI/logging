<?php

namespace KieranFYI\Logging\Providers;

use Illuminate\Support\ServiceProvider;

class LoggingPackageServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
    }
}
