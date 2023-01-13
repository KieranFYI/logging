<?php

namespace KieranFYI\Logging\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
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
}
