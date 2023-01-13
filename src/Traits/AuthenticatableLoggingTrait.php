<?php

namespace KieranFYI\Logging\Traits;

trait AuthenticatableLoggingTrait
{
    use LoggingTrait;

    /**
     * @var string
     */
    protected string $morphTarget = 'user';

}