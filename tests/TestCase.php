<?php

namespace KieranFYI\Tests\Logging;

use Illuminate\Foundation\Application;
use KieranFYI\Logging\Providers\LoggingPackageServiceProvider;
use KieranFYI\Misc\Providers\MiscPackageServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

class TestCase extends OrchestraTestCase
{
    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Load package service provider.
     *
     * @param Application $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            MiscPackageServiceProvider::class,
            LoggingPackageServiceProvider::class,
        ];
    }
}