<?php

namespace KieranFYI\Logging\Traits;

trait HasLoggingTrait
{
    /**
     * @param mixed $object
     * @return bool
     */
    public static function hasLogging(mixed $object): bool
    {
        return array_key_exists(LoggingTrait::class, (new ReflectionClass($object))->getTraits());
    }

}
