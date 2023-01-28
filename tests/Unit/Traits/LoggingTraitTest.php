<?php

namespace KieranFYI\Tests\Logging\Unit\Traits;

use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Schema;
use KieranFYI\Logging\Models\ModelLog;
use KieranFYI\Tests\Logging\Models\LoggingCustomMorphModel;
use KieranFYI\Tests\Logging\Models\LoggingInvalidMorphModel;
use KieranFYI\Tests\Logging\Models\LoggingModel;
use KieranFYI\Tests\Logging\TestCase;
use TypeError;

class LoggingTraitTest extends TestCase
{
    /**
     * @var LoggingModel
     */
    private LoggingModel $model;

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate')->run();

        Schema::create('logging_models', function ($table) {
            $table->temporary();
            $table->id();
            $table->longText('data')
                ->nullable();
            $table->longText('encrypted')
                ->nullable();
            $table->timestamps();
        });

        $this->model = LoggingModel::create();
    }

    public function testMorphTarget()
    {
        $this->assertIsString($this->model->morphTarget());
        $this->assertEquals('model', $this->model->morphTarget());
    }

    public function testMorphTargetCustom()
    {
        $testModel = new LoggingCustomMorphModel();
        $this->assertIsString($testModel->morphTarget());
        $this->assertEquals('test', $testModel->morphTarget());
    }

    public function testMorphTargetInvalid()
    {
        $testModel = new LoggingInvalidMorphModel();
        $this->expectException(TypeError::class);
        $testModel->morphTarget();
    }

    public function testLogs()
    {
        $this->assertInstanceOf(MorphMany::class, $this->model->logs());
        $this->assertInstanceOf(Collection::class, $this->model->logs);
    }

    /**
     * @depends testLogs
     */
    public function testLog()
    {
        $context = ['key' => 'value'];
        $this->model->log('test', 'Log Test Message', $context);
        $log = $this->model
            ->logs()
            ->orderByDesc('id')
            ->first();

        $this->assertEquals('test', $log->level);
        $this->assertEquals('Log Test Message', $log->message);
        $this->assertIsArray($log->context);
        $this->assertEquals($context, $log->context);
    }

    /**
     * @depends testLog
     */
    public function testLogUser()
    {
        Schema::create('users', function ($table) {
            $table->temporary();
            $table->id();
            $table->timestamps();
        });
        $user = new User();
        $user->save();
        $this->actingAs($user);
        $this->model->log('test', 'Log User Test Message');
        $log = $this->model
            ->logs()
            ->orderByDesc('id')
            ->first();

        $this->assertTrue($user->is($log->user));
    }

    /**
     * @depends testLog
     */
    public function testObserveCreated()
    {
        $log = $this->model
            ->logs()
            ->first();
        $this->assertInstanceOf(ModelLog::class, $log);
        $this->assertEquals('change', $log->level);
        $this->assertEquals('Created', $log->message);
        $this->assertIsArray($log->context);
        $this->assertEquals('created', $log->context['action']);
        $this->assertEquals($this->model->getAttributes(), $log->context['new']);
        $this->assertNull($log->context['old']);
        $this->assertNull($log->context['changes']);
    }

    /**
     * @depends testLog
     */
    public function testObserveUpdate()
    {
        $data = ['test'];
        $encrypted = ['encrypted test'];
        $original = $this->model->getOriginal();
        $this->model->update([
            'data' => $data,
            'encrypted' => $encrypted,
        ]);
        $log = $this->model
            ->logs()
            ->orderByDesc('id')
            ->first();
        $this->assertInstanceOf(ModelLog::class, $log);
        $this->assertEquals('change', $log->level);
        $this->assertEquals('Updated', $log->message);
        $this->assertIsArray($log->context);
        $this->assertEquals('updated', $log->context['action']);
        $this->assertEquals(json_decode(json_encode($this->model->getAttributes()), true), $log->context['new']);
        $this->assertEquals(json_decode(json_encode($original), true), $log->context['old']);
        $this->assertEquals(json_decode(json_encode($this->model->getChanges()), true), $log->context['changes']);

        $this->assertEquals(json_encode($data), $log->context['new']['data']);
        $this->assertEquals(json_encode($encrypted), Crypt::decryptString($log->context['new']['encrypted']));
    }

    /**
     * @depends testLog
     */
    public function testObserveDelete()
    {
        $original = $this->model->getOriginal();
        $this->model->delete();
        $log = $this->model
            ->logs()
            ->orderByDesc('id')
            ->first();
        $this->assertInstanceOf(ModelLog::class, $log);
        $this->assertEquals('change', $log->level);
        $this->assertEquals('Deleted', $log->message);
        $this->assertIsArray($log->context);
        $this->assertEquals('deleted', $log->context['action']);
        $this->assertNull($log->context['new']);
        $this->assertEquals(json_decode(json_encode($original), true), $log->context['old']);
        $this->assertNull($log->context['changes']);
    }

    /**
     * @depends testLog
     */
    public function testException()
    {
        $this->model->exception('test', 'Exception message');
        $log = $this->model
            ->logs()
            ->orderByDesc('id')
            ->first();
        $this->assertEquals('test', $log->level);
        $this->assertEquals('Exception message', $log->message);
    }

    /**
     * @depends testLog
     */
    public function testExceptionWithThrowable()
    {
        $throwable = new Exception();
        $this->model->exception('test', $throwable);
        $log = $this->model
            ->logs()
            ->orderByDesc('id')
            ->first();
        $this->assertEquals('test', $log->level);
        $this->assertEquals($throwable->getMessage(), $log->message);
        $this->assertIsArray($log->context['context']);
        $this->assertIsArray($log->context['trace']);
        $this->assertEquals(json_decode(json_encode($throwable->getTrace()), true), $log->context['trace']);
    }

    /**
     * @depends testLog
     */
    public function testDebug()
    {
        $this->model->debug('Debug message');
        $log = $this->model
            ->logs()
            ->orderByDesc('id')
            ->first();
        $this->assertEquals('debug', $log->level);
        $this->assertEquals('Debug message', $log->message);
    }

    /**
     * @depends testLog
     */
    public function testInfo()
    {
        $this->model->info('Info message');
        $log = $this->model
            ->logs()
            ->orderByDesc('id')
            ->first();
        $this->assertEquals('info', $log->level);
        $this->assertEquals('Info message', $log->message);
    }

    /**
     * @depends testLog
     */
    public function testNotice()
    {
        $this->model->notice('Notice message');
        $log = $this->model
            ->logs()
            ->orderByDesc('id')
            ->first();
        $this->assertEquals('notice', $log->level);
        $this->assertEquals('Notice message', $log->message);
    }

    /**
     * @depends testLog
     */
    public function testWarning()
    {
        $this->model->warning('Warning message');
        $log = $this->model
            ->logs()
            ->orderByDesc('id')
            ->first();
        $this->assertEquals('warning', $log->level);
        $this->assertEquals('Warning message', $log->message);
    }

    /**
     * @depends testLog
     */
    public function testAlert()
    {
        $this->model->alert('Alert message');
        $log = $this->model
            ->logs()
            ->orderByDesc('id')
            ->first();
        $this->assertEquals('alert', $log->level);
        $this->assertEquals('Alert message', $log->message);
    }

    /**
     * @depends testLog
     */
    public function testError()
    {
        $this->model->error('Error message');
        $log = $this->model
            ->logs()
            ->orderByDesc('id')
            ->first();
        $this->assertEquals('error', $log->level);
        $this->assertEquals('Error message', $log->message);
    }

    /**
     * @depends testLog
     */
    public function testCritical()
    {
        $this->model->critical('Critical message');
        $log = $this->model
            ->logs()
            ->orderByDesc('id')
            ->first();
        $this->assertEquals('critical', $log->level);
        $this->assertEquals('Critical message', $log->message);
    }

    /**
     * @depends testLog
     */
    public function testEmergency()
    {
        $this->model->emergency('Emergency message');
        $log = $this->model
            ->logs()
            ->orderByDesc('id')
            ->first();
        $this->assertEquals('emergency', $log->level);
        $this->assertEquals('Emergency message', $log->message);
    }

    /**
     * @depends testLog
     */
    public function testSecurity()
    {
        $this->model->security('Security message');
        $log = $this->model
            ->logs()
            ->orderByDesc('id')
            ->first();
        $this->assertEquals('security', $log->level);
        $this->assertEquals('Security message', $log->message);
    }
}