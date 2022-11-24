<?php

namespace KieranFYI\Logging\Traits;

trait AuthenticatableLoggingTrait
{
    use LoggingTrait;

    protected string $morphTarget = 'user';

}