<?php

namespace KieranFYI\Logging\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use KieranFYI\Logging\Models\Logs\ModelLog;

class AuthenticatableLoggingTrait
{
    use LoggingTrait;

    /**
     * @return MorphMany
     */
    public function logs(): MorphMany
    {
        return $this->morphMany(ModelLog::class, 'user');
    }

}