<?php

namespace KieranFYI\Logging\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use KieranFYI\Logging\Traits\HasLoggingTrait;
use KieranFYI\Logging\Traits\LoggingTrait;
use KieranFYI\Misc\Traits\ImmutableTrait;

/**
 * @property string $level
 * @property string $message
 * @property array $context
 * @property array $data
 * @property Model $model
 * @property Model $user
 */
class ModelLog extends Model
{
    use SoftDeletes;
    use ImmutableTrait;
    use HasLoggingTrait;

    // FROM_UNIXTIME(FLOOR(UNIX_TIMESTAMP(datetime)/300)*300)

    /**
     * @var string[]
     */
    protected $fillable = [
        'level', 'message', 'context', 'data'
    ];

    /**
     * @var string[]
     */
    protected $casts = [
        'context' => 'encrypted:array',
        'data' => 'encrypted:array',
    ];

    /**
     * @var string[]
     */
    protected $visible = [
        'id', 'user_title', 'model_title', 'level', 'message', 'context', 'data', 'created_at', 'updated_at'
    ];

    protected $hidden = [
        'model_id', 'model_type', 'user_id', 'user_type', 'deleted_at'
    ];

    /**
     * @var string[]
     */
    protected $appends = [
        'user_title', 'model_title'
    ];

    /**
     * @return MorphTo
     */
    public function model(): MorphTo
    {
        return $this->morphTo()->setEagerLoads([]);
    }

    /**
     * @return MorphTo
     */
    public function user(): MorphTo
    {
        return $this->morphTo()->setEagerLoads([]);
    }

    /**
     * @return string
     */
    public function getUserTitleAttribute(): string
    {
        return $this->classTitle($this->user, 'Unknown User');
    }

    /**
     * @return string
     */
    public function getModelTitleAttribute(): string
    {
        return $this->classTitle($this->model);
    }

    /**
     * @param Model|null $model
     * @param string|null $default
     * @return string
     */
    private function classTitle(?Model $model, string $default = null): string
    {
        if (is_null($model)) {
            return 'N/A';
        }

        $parts = explode('\\', get_class($model));
        $className = array_pop($parts);

        if (!$this->hasLogging($model)) {
            return $className . ': ' . $model->getKey();
        }

        /** @var LoggingTrait $model */
        return $className . ': ' . $model->getAttribute($model->title());
    }
}
