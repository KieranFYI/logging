<?php

namespace KieranFYI\Logging\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use KieranFYI\Logging\Models\ModelLog;

class LoggingPackageServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot()
    {
        Relation::morphMap([
            'modelLog' => ModelLog::class
        ]);

        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
    }
}
