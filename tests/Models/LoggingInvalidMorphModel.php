<?php

namespace KieranFYI\Tests\Logging\Models;

use Illuminate\Database\Eloquent\Model;
use KieranFYI\Logging\Traits\LoggingTrait;

class LoggingInvalidMorphModel extends Model
{
    use LoggingTrait;

    /**
     * @var array
     */
    public array $morphTarget = [];
}