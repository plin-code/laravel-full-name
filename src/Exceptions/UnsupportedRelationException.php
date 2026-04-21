<?php

namespace PlinCode\LaravelFullName\Exceptions;

use InvalidArgumentException;

class UnsupportedRelationException extends InvalidArgumentException
{
    public static function forRelationType(
        string $relationName,
        string $modelClass,
        string $actualType,
    ): self {
        return new self(
            "Relation '{$relationName}' on {$modelClass} must be BelongsTo. "
            ."Got {$actualType}."
        );
    }

    public static function forMissingRelation(
        string $relationName,
        string $modelClass,
    ): self {
        return new self(
            "Relation '{$relationName}' is not defined on {$modelClass}."
        );
    }
}
