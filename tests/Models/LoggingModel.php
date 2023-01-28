<?php

namespace KieranFYI\Tests\Logging\Models;

use Illuminate\Database\Eloquent\Model;
use KieranFYI\Logging\Traits\LoggingTrait;

class LoggingModel extends Model
{
    use LoggingTrait;

    /**
     * @var string[]
     */
    protected $fillable = [
        'data', 'encrypted'
    ];

    /**
     * @var string[]
     */
    protected $casts = [
        'data' => 'array',
        'encrypted' => 'encrypted:array',
    ];
}