<?php

use Filament\Tables\Columns\TextColumn;
use PlinCode\LaravelFullName\Tests\Fixtures\Person;

it('registers a sortable query closure on TextColumn via fullNameSortable', function () {
    $bianchi = Person::create(['first_name' => 'Anna', 'last_name' => 'Bianchi'])->id;
    $azzi = Person::create(['first_name' => 'Anna', 'last_name' => 'Azzi'])->id;

    $column = TextColumn::make('full_name')->fullNameSortable();

    $query = Person::query();

    $reflection = new ReflectionObject($column);
    $property = $reflection->getProperty('sortQuery');
    $property->setAccessible(true);
    $callback = $property->getValue($column);

    expect($callback)->toBeCallable();

    $callback($query, 'asc');

    expect($query->pluck('id')->all())->toBe([$azzi, $bianchi]);
});
