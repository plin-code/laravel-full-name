<?php

use PlinCode\LaravelFullName\Exceptions\InvalidSortDirectionException;
use PlinCode\LaravelFullName\Support\FullNameMatcher;
use PlinCode\LaravelFullName\Support\FullNameOptions;
use PlinCode\LaravelFullName\Tests\Fixtures\Person;

function sortedIds(string $direction, array $options = []): array
{
    $query = Person::query();
    FullNameMatcher::applySort($query, $direction, new FullNameOptions(...$options));

    return $query->pluck('id')->all();
}

it('orders ascending by last_name then first_name', function () {
    $b = Person::create(['first_name' => 'Anna', 'last_name' => 'Bianchi'])->id;
    $a1 = Person::create(['first_name' => 'Giulia', 'last_name' => 'Azzi'])->id;
    $a2 = Person::create(['first_name' => 'Anna', 'last_name' => 'Azzi'])->id;

    expect(sortedIds('asc'))->toBe([$a2, $a1, $b]);
});

it('orders descending', function () {
    $b = Person::create(['first_name' => 'Anna', 'last_name' => 'Bianchi'])->id;
    $a1 = Person::create(['first_name' => 'Giulia', 'last_name' => 'Azzi'])->id;
    $a2 = Person::create(['first_name' => 'Anna', 'last_name' => 'Azzi'])->id;

    expect(sortedIds('desc'))->toBe([$b, $a1, $a2]);
});

it('accepts uppercase direction', function () {
    Person::create(['first_name' => 'Anna', 'last_name' => 'Azzi']);

    expect(fn () => sortedIds('ASC'))->not->toThrow(InvalidSortDirectionException::class);
});

it('throws on invalid direction', function () {
    expect(fn () => sortedIds('sideways'))
        ->toThrow(InvalidSortDirectionException::class, "'asc' or 'desc'");
});

it('supports custom column names', function () {
    $b = Person::create(['given_name' => 'Anna', 'family_name' => 'Bianchi'])->id;
    $a = Person::create(['given_name' => 'Giulia', 'family_name' => 'Azzi'])->id;

    $result = sortedIds('asc', [
        'firstNameColumn' => 'given_name',
        'lastNameColumn' => 'family_name',
    ]);

    expect($result)->toBe([$a, $b]);
});
