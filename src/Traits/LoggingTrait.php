<?php

namespace KieranFYI\Logging\Traits;

use Exception;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Auth;
use KieranFYI\Logging\Models\ModelLog;
use Throwable;
use TypeError;

/**
 * @property string $morphTarget
 *
 * @mixin Model
 */
trait LoggingTrait
{
    use HasLoggingTrait;

    public static function bootLoggingTrait(): void
    {
        static::created(function (self $model) {
            static::observeChanges($model, 'created');
        });

        static::updated(function (self $model) {
            static::observeChanges($model, 'updated');
        });

        static::deleted(function (self $model) {
            static::observeChanges($model, 'deleted');
        });
    }

    /**
     * Get the policies defined on the provider.
     *
     * @return string
     */
    public function morphTarget(): string
    {
        if (property_exists($this, 'morphTarget')) {
            if (!is_string($this->morphTarget)) {
                throw new TypeError(self::class . '::morphTarget(): Property ($morphTarget) must be of type string');
            }

            return $this->morphTarget;
        }
        return 'model';
    }

    /**
     * @return MorphMany
     */
    public function logs(): MorphMany
    {
        return $this->morphMany(ModelLog::class, $this->morphTarget());
    }

    /**
     * @param string $level
     * @param string $message
     * @param array|Arrayable $context
     */
    public function log(string $level, string $message, array|Arrayable $context = []): void
    {
        $log = new ModelLog([
            'level' => $level,
            'message' => $message,
            'context' => $context instanceof Arrayable ? $context->toArray() : $context,
            'data' => $this->toArray(),
        ]);
        /** @var Model $this */
        $log->model()->associate($this);

        if (is_a(Auth::user(), Model::class, true)) {
            /** @var Model $user */
            $user = Auth::user();
            $log->user()->associate($user);
        }

        $log->save();
    }

    /**
     * @param string $level
     * @param string|Throwable $message
     * @param array|Arrayable $context
     */
    public function exception(string $level, string|Throwable $message, array|Arrayable $context = []): void
    {
        if ($message instanceof Throwable) {
            $this->log(
                $level,
                $message->getMessage(),
                [
                    'context' => $context instanceof Arrayable ? $context->toArray() : $context,
                    'trace' => $message->getTrace()
                ]
            );
        } else {
            $this->log($level, $message, $context);
        }
    }

    /**
     * @param string $message
     * @param array|Arrayable $context
     */
    public function debug(string $message, array|Arrayable $context = []): void
    {
        $this->log('debug', $message, $context);
    }

    /**
     * @param string $message
     * @param array|Arrayable $context
     */
    public function info(string $message, array|Arrayable $context = []): void
    {
        $this->log('info', $message, $context);
    }

    /**
     * @param string $message
     * @param array|Arrayable $context
     */
    public function notice(string $message, array|Arrayable $context = []): void
    {
        $this->log('notice', $message, $context);
    }

    /**
     * @param string $message
     * @param array|Arrayable $context
     */
    public function warning(string $message, array|Arrayable $context = []): void
    {
        $this->log('warning', $message, $context);
    }

    /**
     * @param string $message
     * @param array|Arrayable $context
     */
    public function alert(string $message, array|Arrayable $context = []): void
    {
        $this->log('alert', $message, $context);
    }

    /**
     * @param string|Exception $message
     * @param array|Arrayable $context
     */
    public function error(string|Exception $message, array|Arrayable $context = []): void
    {
        $this->exception('error', $message, $context);
    }

    /**
     * @param string|Exception $message
     * @param array|Arrayable $context
     */
    public function critical(string|Exception $message, array|Arrayable $context = []): void
    {
        $this->exception('critical', $message, $context);
    }

    /**
     * @param string|Exception $message
     * @param array|Arrayable $context
     */
    public function emergency(string|Exception $message, array|Arrayable $context = []): void
    {
        $this->exception('emergency', $message, $context);
    }

    /**
     * @param string $message
     * @param array|Arrayable $context
     */
    public function security(string $message, array|Arrayable $context = []): void
    {
        $this->log('security', $message, $context);
    }

    /**
     * @param LoggingTrait $model
     * @param string $action
     *
     * @return void
     */
    private static function observeChanges(self $model, string $action): void
    {
        $model->log(
            'change',
            ucfirst($action),
            [
                'action' => $action,
                'new' => $action !== 'deleted' ? $model->getAttributes() : null,
                'old' => $action !== 'created' ? $model->getOriginal() : null,
                'changes' => $action === 'updated' ? $model->getChanges() : null,
            ]);
    }
}
