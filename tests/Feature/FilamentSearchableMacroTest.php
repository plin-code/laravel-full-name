<?php

use Filament\Tables\Columns\TextColumn;
use PlinCode\LaravelFullName\Tests\Fixtures\Person;

it('registers a searchable query closure on TextColumn via fullNameSearchable', function () {
    Person::create(['first_name' => 'Mario', 'last_name' => 'Rossi']);
    Person::create(['first_name' => 'Luigi', 'last_name' => 'Verdi']);

    $column = TextColumn::make('full_name')->fullNameSearchable();

    $query = Person::query();

    $reflection = new ReflectionObject($column);
    $property = $reflection->getProperty('searchQuery');
    $callback = $property->getValue($column);

    expect($callback)->toBeCallable();

    $callback($query, 'mario rossi');

    expect($query->pluck('first_name')->all())->toBe(['Mario']);
});
