<?php

namespace KieranFYI\Logging\Traits;

use Exception;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Auth;
use KieranFYI\Logging\Models\Logs\ModelLog;

/**
 * @mixin Model
 */
trait LoggingTrait
{
    use HasLogging;

    /**
     * @return MorphMany
     */
    public function logs(): MorphMany
    {
        return $this->morphMany(ModelLog::class, 'model');
    }

    /**
     * @param string $level
     * @param string $message
     * @param array|Arrayable $context
     */
    public function log(string $level, string $message, array|Arrayable $context = [], string $user_message = null): void
    {
        $log = new ModelLog([
            'level' => $level,
            'message' => $message,
            'context' => $context instanceof Arrayable ? $context->toArray() : $context,
            'data' => $this->toArray(),
        ]);

        /** @var Model $this */
        $log->model()->associate($this);

        if (
            Auth::hasUser()
            && (
                Auth::user() !== $this
                && in_array(Model::class, class_uses_recursive(Auth::user()))
            )
        ) {
            /** @var Model $user */
            $user = Auth::user();
            $log->user()->associate($user);

            if (!empty($user_message) && $this->hasLogging($user)) {
                /** @var LoggingTrait $user */
                $user->log($level, $message, $context);
            }
        }

        $log->save();
    }

    /**
     * @param string $level
     * @param string|Exception $message
     * @param array|Arrayable $context
     */
    public function exception(string $level, string|Exception $message, array|Arrayable $context = [], string $user_message = null): void
    {
        if ($message instanceof Exception) {
            $this->log(
                $level,
                $message->getMessage(),
                [
                    'context' => $context instanceof Arrayable ? $context->toArray() : $context,
                    'trace' => $message->getTrace()
                ],
                $user_message
            );
        } else {
            $this->log($level, $message, $context, $user_message);
        }
    }

    /**
     * @param string $message
     * @param array|Arrayable $context
     */
    public function debug(string $message, array|Arrayable $context = [], string $user_message = null): void
    {
        $this->log('debug', $message, $context, $user_message);
    }

    /**
     * @param string $message
     * @param array|Arrayable $context
     */
    public function info(string $message, array|Arrayable $context = [], string $user_message = null): void
    {
        $this->log('info', $message, $context, $user_message);
    }

    /**
     * @param string $message
     * @param array|Arrayable $context
     */
    public function notice(string $message, array|Arrayable $context = [], string $user_message = null): void
    {
        $this->log('notice', $message, $context, $user_message);
    }

    /**
     * @param string $message
     * @param array|Arrayable $context
     */
    public function warning(string $message, array|Arrayable $context = [], string $user_message = null): void
    {
        $this->log('warning', $message, $context, $user_message);
    }

    /**
     * @param string $message
     * @param array|Arrayable $context
     */
    public function alert(string $message, array|Arrayable $context = [], string $user_message = null): void
    {
        $this->log('alert', $message, $context, $user_message);
    }

    /**
     * @param string|Exception $message
     * @param array|Arrayable $context
     */
    public function error(string|Exception $message, array|Arrayable $context = [], string $user_message = null): void
    {
        $this->exception('error', $message, $context, $user_message);
    }

    /**
     * @param string|Exception $message
     * @param array|Arrayable $context
     */
    public function critical(string|Exception $message, array|Arrayable $context = [], string $user_message = null): void
    {
        $this->exception('critical', $message, $context, $user_message);
    }

    /**
     * @param string|Exception $message
     * @param array|Arrayable $context
     */
    public function emergency(string|Exception $message, array|Arrayable $context = [], string $user_message = null): void
    {
        $this->exception('emergency', $message, $context, $user_message);
    }

    /**
     * @param string $message
     * @param array|Arrayable $context
     */
    public function security(string $message, array|Arrayable $context = [], string $user_message = null): void
    {
        $this->log('security', $message, $context, $user_message);
    }
}
