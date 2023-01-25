<?php

namespace KieranFYI\Tests\Logging\Unit\Http\Requests;

use KieranFYI\Logging\Http\Requests\LogSearchRequest;
use KieranFYI\Tests\Logging\TestCase;
class LogSearchRequestTest extends TestCase
{
    public function testRules()
    {
        $request = new LogSearchRequest();
        $rules = $request->rules();
        $this->assertIsArray($rules);

        $this->assertArrayHasKey('page', $rules);
        $this->assertContains('nullable', $rules['page']);
        $this->assertContains('integer', $rules['page']);

        $this->assertArrayHasKey('limit', $rules);
        $this->assertContains('nullable', $rules['limit']);
        $this->assertContains('integer', $rules['limit']);
        $this->assertContains('min:1', $rules['limit']);
        $this->assertContains('max:1000', $rules['limit']);

        $this->assertArrayHasKey('user', $rules);
        $this->assertContains('nullable', $rules['user']);
        $this->assertContains('string', $rules['user']);
    }
}