<?php

namespace PlinCode\LaravelFullName\Support;

use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Query\JoinClause;
use PlinCode\LaravelFullName\Exceptions\InvalidSortDirectionException;
use PlinCode\LaravelFullName\Exceptions\UnsupportedRelationException;

final class FullNameMatcher
{
    public const LIKE_ESCAPE_CHAR = '!';

    /**
     * @template TModel of \Illuminate\Database\Eloquent\Model
     *
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
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

    /**
     * @template TModel of \Illuminate\Database\Eloquent\Model
     *
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
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

    /**
     * @template TModel of \Illuminate\Database\Eloquent\Model
     *
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    private static function applySortWithRelation(
        Builder $query,
        string $direction,
        FullNameOptions $options,
    ): Builder {
        /** @var string $relationName */
        $relationName = $options->relation;
        self::assertBelongsTo($query, $relationName);

        /** @var BelongsTo<Model, Model> $relation */
        $relation = $query->getModel()->{$relationName}();
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

    /**
     * Detect whether the target table is already joined on the query.
     *
     * Matches plain string table references, `joinSub` Expression aliases with
     * double-quoted (SQLite, PostgreSQL) or backtick (MySQL) quoting. Custom
     * alias patterns or SQL Server bracket quoting are not detected and may
     * result in a duplicate join. Callers performing manual joins should
     * avoid calling `orderByFullName` on a query that already carries a join
     * to the related table under a non-standard alias.
     *
     * @template TModel of \Illuminate\Database\Eloquent\Model
     *
     * @param  Builder<TModel>  $query
     */
    private static function alreadyJoined(Builder $query, string $table): bool
    {
        /** @var array<JoinClause> $joins */
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

    /**
     * @template TModel of \Illuminate\Database\Eloquent\Model
     *
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
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

    /**
     * @template TModel of \Illuminate\Database\Eloquent\Model
     *
     * @param  Builder<TModel>  $query
     */
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
            $relationClass = is_object($relation) ? class_basename($relation) : gettype($relation);
            throw UnsupportedRelationException::forRelationType(
                $relationName,
                $modelClass,
                $relationClass,
            );
        }
    }
}
