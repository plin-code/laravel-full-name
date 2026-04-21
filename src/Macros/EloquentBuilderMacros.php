<?php

namespace PlinCode\LaravelFullName\Macros;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use PlinCode\LaravelFullName\Support\FullNameMatcher;
use PlinCode\LaravelFullName\Support\FullNameOptions;

final class EloquentBuilderMacros
{
    public static function register(): void
    {
        Builder::macro('searchFullName', function (
            string $search,
            ?string $relation = null,
            string $firstNameColumn = 'first_name',
            string $lastNameColumn = 'last_name',
        ) {
            /** @var Builder<Model> $this */
            return FullNameMatcher::applySearch(
                $this,
                $search,
                new FullNameOptions(
                    relation: $relation,
                    firstNameColumn: $firstNameColumn,
                    lastNameColumn: $lastNameColumn,
                ),
            );
        });

        Builder::macro('orderByFullName', function (
            string $direction = 'asc',
            ?string $relation = null,
            string $firstNameColumn = 'first_name',
            string $lastNameColumn = 'last_name',
        ) {
            /** @var Builder<Model> $this */
            return FullNameMatcher::applySort(
                $this,
                $direction,
                new FullNameOptions(
                    relation: $relation,
                    firstNameColumn: $firstNameColumn,
                    lastNameColumn: $lastNameColumn,
                ),
            );
        });
    }
}
