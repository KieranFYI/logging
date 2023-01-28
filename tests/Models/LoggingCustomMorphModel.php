<?php

namespace KieranFYI\Tests\Logging\Models;

use Illuminate\Database\Eloquent\Model;
use KieranFYI\Logging\Traits\LoggingTrait;

class LoggingCustomMorphModel extends Model
{
    use LoggingTrait;

    /**
     * @var string
     */
    public string $morphTarget = 'test';
}