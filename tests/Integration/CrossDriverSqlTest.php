<?php

pest()->group('integration');

use PlinCode\LaravelFullName\Support\FullNameMatcher;
use PlinCode\LaravelFullName\Support\FullNameOptions;
use PlinCode\LaravelFullName\Tests\Fixtures\Person;

function withDriver(string $driver, Closure $setup): void
{
    if (env('LFN_INTEGRATION_DRIVER') !== $driver) {
        test()->markTestSkipped("Driver {$driver} not configured for this run.");
    }

    $setup();
}

dataset('drivers', [
    'mysql' => ['mysql'],
    'pgsql' => ['pgsql'],
]);

it('matches single token across drivers', function (string $driver) {
    withDriver($driver, function () {
        Person::create(['first_name' => 'Mario', 'last_name' => 'Rossi']);
        Person::create(['first_name' => 'Luigi', 'last_name' => 'Verdi']);

        $query = Person::query();
        FullNameMatcher::applySearch($query, 'mario', new FullNameOptions);

        expect($query->pluck('first_name')->all())->toBe(['Mario']);
    });
})->with('drivers');

it('matches multi token across drivers', function (string $driver) {
    withDriver($driver, function () {
        Person::create(['first_name' => 'Mario', 'last_name' => 'Rossi']);
        Person::create(['first_name' => 'Mariacarmela', 'last_name' => 'Rossi']);

        $query = Person::query();
        FullNameMatcher::applySearch($query, 'mario rossi', new FullNameOptions);

        expect($query->pluck('first_name')->all())->toBe(['Mario']);
    });
})->with('drivers');

it('escapes wildcards across drivers', function (string $driver) {
    withDriver($driver, function () {
        Person::create(['first_name' => '50%', 'last_name' => 'Sconto']);
        Person::create(['first_name' => 'Mario', 'last_name' => 'Rossi']);

        $query = Person::query();
        FullNameMatcher::applySearch($query, '50%', new FullNameOptions);

        expect($query->pluck('first_name')->all())->toBe(['50%']);
    });
})->with('drivers');

it('lowercases unicode across drivers', function (string $driver) {
    withDriver($driver, function () {
        Person::create(['first_name' => 'María', 'last_name' => 'Rossi']);

        $query = Person::query();
        FullNameMatcher::applySearch($query, 'maría rossi', new FullNameOptions);

        expect($query->count())->toBe(1);
    });
})->with('drivers');
