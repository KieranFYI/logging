<?php

namespace KieranFYI\Tests\Logging\Unit\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Schema;
use KieranFYI\Logging\Http\Requests\LogSearchRequest;
use KieranFYI\Logging\Models\ModelLog;
use KieranFYI\Logging\Traits\LoggableResponse;
use KieranFYI\Tests\Logging\Models\BasicModel;
use KieranFYI\Tests\Logging\Models\LoggingModel;
use KieranFYI\Tests\Logging\TestCase;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class LoggableResponseTraitTest extends TestCase
{
    use LoggableResponse;

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new ModelLog();

        $this->artisan('migrate')->run();
    }

    /**
     * @throws Throwable
     */
    public function testLoggableResponse()
    {
        $logSearchRequest = $this->app->make(LogSearchRequest::class);
        $testModel = new LoggingModel();
        $this->assertInstanceOf(JsonResponse::class, $this->loggableResponse($logSearchRequest, $testModel));
    }

    /**
     * @throws Throwable
     */
    public function testLoggableResponseInvalidModel()
    {
        $logSearchRequest = $this->app->make(LogSearchRequest::class);
        $testModel = new BasicModel();
        $this->expectException(HttpException::class);
        $this->loggableResponse($logSearchRequest, $testModel);
    }
}