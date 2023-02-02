<?php

namespace KieranFYI\Tests\Logging\Unit\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Schema;
use KieranFYI\Logging\Models\ModelLog;
use KieranFYI\Logging\Traits\HasLoggingTrait;
use KieranFYI\Misc\Traits\ImmutableTrait;
use KieranFYI\Misc\Traits\KeyedTitle;
use KieranFYI\Tests\Logging\Models\BasicModel;
use KieranFYI\Tests\Logging\Models\KeyedTitleModel;
use KieranFYI\Tests\Logging\TestCase;

class ModelLogTest extends TestCase
{

    /**
     * @var ModelLog
     */
    private ModelLog $model;

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new ModelLog();

        Schema::create('keyed_title_models', function ($table) {
            $table->temporary();
            $table->id();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('basic_models', function ($table) {
            $table->temporary();
            $table->id();
            $table->timestamps();
        });
    }

    public function testModel()
    {
        $this->assertInstanceOf(Model::class, $this->model);
    }

    public function testTraits()
    {
        $uses = class_uses_recursive(ModelLog::class);
        $this->assertContains(SoftDeletes::class, $uses);
        $this->assertContains(ImmutableTrait::class, $uses);
        $this->assertContains(HasLoggingTrait::class, $uses);
        $this->assertContains(KeyedTitle::class, $uses);
    }

    public function testFillable()
    {
        $fillable = [
            'level', 'message', 'context', 'data'
        ];
        $this->assertEquals($fillable, $this->model->getFillable());
    }

    public function testCasts()
    {
        $casts = [
            'context' => 'encrypted:array',
            'data' => 'encrypted:array',
            'id' => 'int',
            'deleted_at' => 'datetime',
        ];

        $this->assertEquals($casts, $this->model->getCasts());
    }

    public function testVisible()
    {
        $visible = [
            'id', 'user_title', 'model_title', 'level', 'message', 'context', 'data', 'created_at', 'updated_at'
        ];

        $this->assertEquals($visible, $this->model->getVisible());
    }

    public function testHidden()
    {
        $hidden = [
            'model_id', 'model_type', 'user_id', 'user_type', 'deleted_at'
        ];

        $this->assertEquals($hidden, $this->model->getHidden());
    }

    public function testAppends()
    {
        $appends = [
            'user_title', 'model_title', 'title', 'title_detailed'
        ];
        $this->assertEquals($appends, $this->model->getAppends());
    }

    public function testUserRelation()
    {
        $this->assertInstanceOf(MorphTo::class, $this->model->user());

        $testUser = BasicModel::create([]);
        $this->assertTrue($this->model->is($this->model->user()->associate($testUser)));
        $this->assertTrue($testUser->is($this->model->user));
    }

    /**
     * @depends testUserRelation
     */
    public function testGetUserTitleAttributeNull()
    {
        $this->assertIsString($this->model->getUserTitleAttribute());
        $this->assertIsString($this->model->user_title);
        $this->assertEquals('Unknown', $this->model->getUserTitleAttribute());
    }

    /**
     * @depends testUserRelation
     */
    public function testGetUserTitleAttributeWithUser()
    {
        $testModel = KeyedTitleModel::create([]);
        $this->model->user()->associate($testModel);

        $this->assertIsString($this->model->getUserTitleAttribute());
        $this->assertIsString($this->model->user_title);
        $this->assertEquals('KeyedTitleModel: 1', $this->model->getUserTitleAttribute());
    }

    /**
     * @depends testUserRelation
     */
    public function testGetUserTitleAttributeWithUserSoftDeleted()
    {
        $testModel = KeyedTitleModel::create([]);
        $this->model->user()->associate($testModel);
        $testModel->delete();

        $this->assertIsString($this->model->getUserTitleAttribute());
        $this->assertIsString($this->model->user_title);
        $this->assertEquals('KeyedTitleModel: 1 (Soft Deleted)', $this->model->getUserTitleAttribute());
    }

    /**
     * @depends testUserRelation
     */
    public function testGetUserTitleAttributeWithUserDeleted()
    {
        $this->model->setAttribute('user_id', 1);
        $this->model->setAttribute('user_type', KeyedTitleModel::class);

        $this->assertIsString($this->model->getUserTitleAttribute());
        $this->assertIsString($this->model->user_title);
        $this->assertEquals('KeyedTitleModel: 1 (Deleted)', $this->model->getUserTitleAttribute());
    }

    /**
     * @depends testUserRelation
     */
    public function testGetUserTitleAttributeWithBasicModel()
    {
        $testModel = BasicModel::create([]);
        $this->model->user()->associate($testModel);

        $this->assertIsString($this->model->getUserTitleAttribute());
        $this->assertIsString($this->model->user_title);
        $this->assertEquals('BasicModel: 1', $this->model->getUserTitleAttribute());
    }

    public function testModelRelation()
    {
        $this->assertInstanceOf(MorphTo::class, $this->model->model());

        $testUser = KeyedTitleModel::create([]);
        $this->assertTrue($this->model->is($this->model->user()->associate($testUser)));
        $this->assertTrue($testUser->is($this->model->user));
    }

    /**
     * @depends testModelRelation
     */
    public function testGetModelTitleAttributeNull()
    {
        $this->assertIsString($this->model->getModelTitleAttribute());
        $this->assertIsString($this->model->model_title);
        $this->assertEquals('Unknown', $this->model->getModelTitleAttribute());
    }

    /**
     * @depends testModelRelation
     */
    public function testGetModelTitleAttributeWithModel()
    {
        $testModel = KeyedTitleModel::create([]);
        $this->model->model()->associate($testModel);

        $this->assertIsString($this->model->getModelTitleAttribute());
        $this->assertIsString($this->model->model_title);
        $this->assertEquals('KeyedTitleModel: 1', $this->model->getModelTitleAttribute());
    }

    /**
     * @depends testModelRelation
     */
    public function testGetModelTitleAttributeWithModelSoftDeleted()
    {
        $testModel = KeyedTitleModel::create([]);
        $this->model->model()->associate($testModel);
        $testModel->delete();

        $this->assertIsString($this->model->getModelTitleAttribute());
        $this->assertIsString($this->model->model_title);
        $this->assertEquals('KeyedTitleModel: 1 (Soft Deleted)', $this->model->getModelTitleAttribute());
    }

    /**
     * @depends testModelRelation
     */
    public function testGetModelTitleAttributeWithModelDeleted()
    {
        $this->model->setAttribute('model_id', 1);
        $this->model->setAttribute('model_type', KeyedTitleModel::class);

        $this->assertIsString($this->model->getModelTitleAttribute());
        $this->assertIsString($this->model->model_title);
        $this->assertEquals('KeyedTitleModel: 1 (Deleted)', $this->model->getModelTitleAttribute());
    }

    /**
     * @depends testModelRelation
     */
    public function testGetModelTitleAttributeWithModelDeletedAndData()
    {
        $this->model->setAttribute('model_id', 1);
        $this->model->setAttribute('model_type', KeyedTitleModel::class);
        $this->model->setAttribute('data', [
            'id' => 1,
            'deleted_at' => Carbon::now(),
        ]);

        $this->assertIsString($this->model->getModelTitleAttribute());
        $this->assertIsString($this->model->model_title);
        $this->assertEquals('KeyedTitleModel: 1 (Deleted)', $this->model->getModelTitleAttribute());
    }

    /**
     * @depends testModelRelation
     */
    public function testGetModelTitleAttributeWithBasicModel()
    {
        $testModel = BasicModel::create([]);
        $this->model->model()->associate($testModel);

        $this->assertIsString($this->model->getModelTitleAttribute());
        $this->assertIsString($this->model->model_title);
        $this->assertEquals('BasicModel: 1', $this->model->getModelTitleAttribute());
    }
}