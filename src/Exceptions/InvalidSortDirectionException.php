<?php

namespace PlinCode\LaravelFullName\Exceptions;

use InvalidArgumentException;

class InvalidSortDirectionException extends InvalidArgumentException
{
    public static function fromDirection(string $direction): self
    {
        return new self(
            "Sort direction must be 'asc' or 'desc', got '{$direction}'."
        );
    }
}
