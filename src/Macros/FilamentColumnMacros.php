<?php

namespace PlinCode\LaravelFullName\Macros;

use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;

final class FilamentColumnMacros
{
    public static function register(): void
    {
        TextColumn::macro('fullNameSearchable', fn (?string $relation = null, string $firstNameColumn = 'first_name', string $lastNameColumn = 'last_name') => $this->searchable(
            query: fn (Builder $query, string $search): Builder => $query->searchFullName($search, $relation, $firstNameColumn, $lastNameColumn),
        ));

        TextColumn::macro('fullNameSortable', fn (?string $relation = null, string $firstNameColumn = 'first_name', string $lastNameColumn = 'last_name') => $this->sortable(
            query: fn (Builder $query, string $direction): Builder => $query->orderByFullName($direction, $relation, $firstNameColumn, $lastNameColumn),
        ));
    }
}
