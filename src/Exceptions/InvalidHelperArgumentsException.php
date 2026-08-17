<?php

namespace Mozex\CommonMarkRoutes\Exceptions;

use InvalidArgumentException;

class InvalidHelperArgumentsException extends InvalidArgumentException
{
    public static function at(string $source, int $position): self
    {
        return new self(sprintf(
            'Unable to parse the helper arguments [%s] at offset %d. Only literal values are supported: strings, numbers, booleans, null, and arrays of those.',
            $source,
            $position
        ));
    }

    public static function unresolvable(string $function): self
    {
        return new self(sprintf(
            'The helper [%s()] did not resolve to a URL. Pass a path or route name as its first argument.',
            $function
        ));
    }
}
