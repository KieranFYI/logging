<?php

namespace KieranFYI\Logging\Models;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use KieranFYI\Logging\Traits\HasLoggingTrait;
use KieranFYI\Misc\Traits\ImmutableTrait;
use KieranFYI\Misc\Traits\KeyedTitle;

/**
 * @property string $level
 * @property string $message
 * @property array $context
 * @property array $data
 * @property Model $model
 * @property Model $user
 * @property string $model_type
 * @property int $model_id
 * @property string $user_type
 * @property int $user_id
 */
class ModelLog extends Model
{
    use SoftDeletes;
    use ImmutableTrait;
    use HasLoggingTrait;
    use KeyedTitle;

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
        return $this->morphTo()
            ->withoutGlobalScopes([SoftDeletingScope::class])
            ->setEagerLoads([]);
    }

    /**
     * @return MorphTo
     */
    public function user(): MorphTo
    {
        return $this->morphTo()
            ->withoutGlobalScopes([SoftDeletingScope::class])
            ->setEagerLoads([]);
    }

    /**
     * @return string
     */
    public function getUserTitleAttribute(): string
    {
        $parts = explode('\\', $this->user_type);
        $className = array_pop($parts);

        if (!is_null($this->user) && method_exists($this->user, 'getTitleDetailedAttribute')) {
            return $this->user->title_detailed;
        }

        return $className . ': ' . $this->user_id;
    }

    /**
     * @return string
     */
    public function getModelTitleAttribute(): string
    {
        $model = $this->model;
        $parts = explode('\\', $this->model_type);
        $className = array_pop($parts);
        $hardDeleted = false;

        if (is_null($model)) {
            $hardDeleted = true;
            try {
                $model = new $this->model_type($this->data);
            } catch (Exception) {
            }
        }
        if (is_null($model) || !in_array(KeyedTitle::class, class_uses_recursive($model))) {
            return $className . ': ' . $this->model_id . ' (Hard Deleted)';
        }

        /** @var KeyedTitle $model */
        if ($hardDeleted) {
            return $className . ': ' . $model->title . ' (Hard Deleted)';
        }

        return $model->title_detailed;
    }
}
