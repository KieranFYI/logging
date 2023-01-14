<?php

namespace KieranFYI\Logging\Traits;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use KieranFYI\Logging\Http\Requests\LogSearchRequest;
use KieranFYI\Logging\Models\ModelLog;
use KieranFYI\Misc\Traits\ResponseCacheable;
use Throwable;

trait LoggableResponse
{
    use HasLoggingTrait;
    use ResponseCacheable;

    /**
     * @param LogSearchRequest $request
     * @param Model $model
     * @return JsonResponse
     * @throws Throwable
     */
    public function loggableResponse(LogSearchRequest $request, Model $model): JsonResponse
    {
        abort_unless($this->hasLogging($model), 501);
        /** @var LoggingTrait $model */

        $this->cached(Carbon::parse($model->logs()->max('updated_at')));

        $logs = $model->logs()
            ->with(['user', 'model']);

        // Search

        $paginator = $logs->paginate($request->validated('limit', 15));
        $paginator->getCollection()->transform(function (ModelLog $log) use ($model) {
            $log->setAttribute('user_title', $this->classTitle($log->user, 'Unknown User'));
            $log->setAttribute('model_title', $this->classTitle($log->model));
            return $log;
        });

        return response()->json($paginator);
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