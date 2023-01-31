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

        $logs = $model->logs()
            ->with(['user', 'model'])
            ->orderByDesc('created_at');

        return response()->json($logs->paginate($request->validated('limit', 15)));
    }
}