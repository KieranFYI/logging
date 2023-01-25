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
 * @property string $user_title
 * @property string $model_title
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
        if (empty($this->user_type)) {
            return 'Unknown';
        }

        $parts = explode('\\', $this->user_type);
        $className = array_pop($parts);
        $title = $className . ': ' . $this->user_id;

        if (is_null($this->user)) {
            return $title . ' (Deleted)';
        }

        if (method_exists($this->user, 'getTitleDetailedAttribute')) {
            return $this->user->getTitleDetailedAttribute();
        }

        return $title;
    }

    /**
     * @return string
     */
    public function getModelTitleAttribute(): string
    {
        if (empty($this->model_type)) {
            return 'Unknown';
        }

        $parts = explode('\\', $this->model_type);
        $className = array_pop($parts);
        $title = $className . ': ' . $this->model_id;

        if (is_null($this->model) && is_array($this->data) && class_exists($this->model_type)) {
            $data = $this->data;
            if (!empty($data['deleted_at'])) {
                unset($data['deleted_at']);
            }
            /** @var Model $model */
            $model = new $this->model_type();
            $model->setRawAttributes($data);

            if (method_exists($model, 'getTitleDetailedAttribute')) {
                return $model->getTitleDetailedAttribute() . ' (Deleted)';
            }
        }

        if (is_null($this->model)) {
            return $title . ' (Deleted)';
        }

        if (method_exists($this->model, 'getTitleDetailedAttribute')) {
            return $this->model->getTitleDetailedAttribute();
        }

        return $title;
    }
}
