<?php

namespace KieranFYI\Tests\Logging\Unit\Traits;

use KieranFYI\Logging\Traits\HasLoggingTrait;
use KieranFYI\Tests\Logging\Models\BasicModel;
use KieranFYI\Tests\Logging\Models\LoggingModel;
use KieranFYI\Tests\Logging\TestCase;

class HasLoggingTraitTest extends TestCase
{
    use HasLoggingTrait;

    public function testHasLogging()
    {
        $testModel = new LoggingModel();
        $this->assertTrue($this->hasLogging($testModel));
    }

    public function testHasLoggingFalse()
    {
        $testModel = new BasicModel();
        $this->assertFalse($this->hasLogging($testModel));
    }
}