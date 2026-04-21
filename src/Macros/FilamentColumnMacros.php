<?php

namespace PlinCode\LaravelFullName\Macros;

use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;

final class FilamentColumnMacros
{
    public static function register(): void
    {
        TextColumn::macro('fullNameSearchable', function (
            ?string $relation = null,
            string $firstNameColumn = 'first_name',
            string $lastNameColumn = 'last_name',
        ) {
            /** @var TextColumn $this */
            return $this->searchable(
                query: fn (Builder $query, string $search): Builder => $query->searchFullName($search, $relation, $firstNameColumn, $lastNameColumn),
            );
        });

        TextColumn::macro('fullNameSortable', function (
            ?string $relation = null,
            string $firstNameColumn = 'first_name',
            string $lastNameColumn = 'last_name',
        ) {
            /** @var TextColumn $this */
            return $this->sortable(
                query: fn (Builder $query, string $direction): Builder => $query->orderByFullName($direction, $relation, $firstNameColumn, $lastNameColumn),
            );
        });
    }
}
