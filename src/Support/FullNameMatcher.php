<?php

namespace PlinCode\LaravelFullName\Support;

use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use PlinCode\LaravelFullName\Exceptions\InvalidSortDirectionException;
use PlinCode\LaravelFullName\Exceptions\UnsupportedRelationException;

final class FullNameMatcher
{
    public const LIKE_ESCAPE_CHAR = '!';

    public static function applySearch(
        Builder $query,
        string $input,
        FullNameOptions $options,
    ): Builder {
        $normalized = self::normalize($input);

        if ($normalized === '') {
            return $query;
        }

        $escaped = self::escapeLike($normalized);
        $pattern = '%'.$escaped.'%';

        if ($options->relation !== null) {
            self::assertBelongsTo($query, $options->relation);

            return $query->whereHas(
                $options->relation,
                fn (Builder $sub) => self::buildSearchWhere($sub, $pattern, $options->withoutRelation()),
            );
        }

        return self::buildSearchWhere($query, $pattern, $options);
    }

    public static function applySort(
        Builder $query,
        string $direction,
        FullNameOptions $options,
    ): Builder {
        $normalizedDirection = strtolower(trim($direction));

        if (! in_array($normalizedDirection, ['asc', 'desc'], true)) {
            throw InvalidSortDirectionException::fromDirection($direction);
        }

        if ($options->relation !== null) {
            return self::applySortWithRelation($query, $normalizedDirection, $options);
        }

        return $query
            ->orderBy($options->lastNameColumn, $normalizedDirection)
            ->orderBy($options->firstNameColumn, $normalizedDirection);
    }

    private static function applySortWithRelation(
        Builder $query,
        string $direction,
        FullNameOptions $options,
    ): Builder {
        self::assertBelongsTo($query, $options->relation);

        $relation = $query->getModel()->{$options->relation}();
        $related = $relation->getRelated();
        $mainTable = $query->getModel()->getTable();
        $relatedTable = $related->getTable();
        $foreignKey = $relation->getForeignKeyName();
        $ownerKey = $relation->getOwnerKeyName();

        if (! self::alreadyJoined($query, $relatedTable)) {
            $query->joinSub(
                $related->newQuery()->select([
                    "{$relatedTable}.{$ownerKey}",
                    "{$relatedTable}.{$options->firstNameColumn}",
                    "{$relatedTable}.{$options->lastNameColumn}",
                ]),
                $relatedTable,
                "{$mainTable}.{$foreignKey}",
                '=',
                "{$relatedTable}.{$ownerKey}",
            );

            if (empty($query->getQuery()->columns)) {
                $query->select("{$mainTable}.*");
            }
        }

        return $query
            ->orderBy("{$relatedTable}.{$options->lastNameColumn}", $direction)
            ->orderBy("{$relatedTable}.{$options->firstNameColumn}", $direction);
    }

    private static function alreadyJoined(Builder $query, string $table): bool
    {
        $joins = $query->getQuery()->joins ?? [];

        foreach ($joins as $join) {
            $joinTable = $join->table instanceof Expression
                ? (string) $join->table->getValue($query->getQuery()->grammar)
                : (string) $join->table;

            if ($joinTable === $table || str_ends_with($joinTable, "as \"{$table}\"") || str_ends_with($joinTable, "as `{$table}`")) {
                return true;
            }
        }

        return false;
    }

    public static function normalize(string $input): string
    {
        $collapsed = preg_replace('/\s+/u', ' ', $input);

        if ($collapsed === null) {
            throw new \RuntimeException(
                'Failed to normalize input: preg_replace returned null (PCRE error code '.preg_last_error().').'
            );
        }

        $trimmed = trim($collapsed);

        return mb_strtolower($trimmed, 'UTF-8');
    }

    public static function escapeLike(string $value): string
    {
        $escape = self::LIKE_ESCAPE_CHAR;

        return str_replace(
            [$escape, '%', '_'],
            [$escape.$escape, $escape.'%', $escape.'_'],
            $value,
        );
    }

    private static function buildSearchWhere(
        Builder $query,
        string $pattern,
        FullNameOptions $options,
    ): Builder {
        $first = $options->firstNameColumn;
        $last = $options->lastNameColumn;
        $escape = self::LIKE_ESCAPE_CHAR;

        return $query->where(function (Builder $sub) use ($pattern, $first, $last, $escape) {
            $sub->whereRaw(
                "LOWER(CONCAT(COALESCE({$first}, ''), ' ', COALESCE({$last}, ''))) LIKE ? ESCAPE '{$escape}'",
                [$pattern],
            )->orWhereRaw(
                "LOWER(CONCAT(COALESCE({$last}, ''), ' ', COALESCE({$first}, ''))) LIKE ? ESCAPE '{$escape}'",
                [$pattern],
            );
        });
    }

    private static function assertBelongsTo(Builder $query, string $relationName): void
    {
        $model = $query->getModel();
        $modelClass = $model::class;

        if (! method_exists($model, $relationName)) {
            throw UnsupportedRelationException::forMissingRelation(
                $relationName,
                $modelClass,
            );
        }

        $relation = $model->{$relationName}();

        if (! $relation instanceof BelongsTo) {
            throw UnsupportedRelationException::forRelationType(
                $relationName,
                $modelClass,
                class_basename($relation),
            );
        }
    }
}
