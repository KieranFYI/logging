<?php

namespace KieranFYI\Logging\Traits;

trait HasLogging
{
    /**
     * @param mixed $object
     * @return bool
     */
    public function hasLogging(mixed $object): bool
    {
        return is_object($object) && in_array(LoggingTrait::class, class_uses_recursive($object));
    }

}