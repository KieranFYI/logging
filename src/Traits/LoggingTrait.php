<?php

namespace KieranFYI\Logging\Traits;

use Exception;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Auth;
use KieranFYI\Logging\Models\Logs\ModelLog;
use TypeError;

/**
 * @property string $morphTarget
 *
 * @mixin Model
 */
trait LoggingTrait
{
    use HasLoggingTrait;

    /**
     * Get the policies defined on the provider.
     *
     * @return string
     */
    public function morphTarget(): string
    {
        if (property_exists($this, 'morphTarget')) {
            if (!is_string($this->morphTarget)) {
                throw new TypeError('Invalid Type on "morphTarget", string expected');
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
        }

        $this->logs()->save($log);
    }

    /**
     * @param string $level
     * @param string|Exception $message
     * @param array|Arrayable $context
     */
    public function exception(string $level, string|Exception $message, array|Arrayable $context = []): void
    {
        if ($message instanceof Exception) {
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
}
