<?php

use PlinCode\LaravelFullName\Support\FullNameMatcher;
use PlinCode\LaravelFullName\Support\FullNameOptions;
use PlinCode\LaravelFullName\Tests\Fixtures\Person;

function applySearch(string $input, array $options = []): array
{
    $query = Person::query();
    FullNameMatcher::applySearch(
        $query,
        $input,
        new FullNameOptions(...$options),
    );

    return $query->pluck('id')->all();
}

function seed(array $names): array
{
    $ids = [];
    foreach ($names as $key => [$first, $last]) {
        $ids[$key] = Person::create([
            'first_name' => $first,
            'last_name' => $last,
        ])->id;
    }

    return $ids;
}

it('returns the query unchanged on empty input', function () {
    $ids = seed(['mario' => ['Mario', 'Rossi']]);

    expect(applySearch(''))->toContain($ids['mario']);
    expect(applySearch('   '))->toContain($ids['mario']);
});

it('matches a single token against first_name', function () {
    $ids = seed([
        'mario' => ['Mario', 'Rossi'],
        'luigi' => ['Luigi', 'Verdi'],
    ]);

    expect(applySearch('mario'))->toBe([$ids['mario']]);
});

it('matches a single token against last_name', function () {
    $ids = seed([
        'mario' => ['Mario', 'Rossi'],
        'luigi' => ['Luigi', 'Verdi'],
    ]);

    expect(applySearch('verdi'))->toBe([$ids['luigi']]);
});

it('matches a single token as a substring', function () {
    $ids = seed([
        'mariacarmela' => ['Mariacarmela', 'Rossi'],
    ]);

    expect(applySearch('maria'))->toBe([$ids['mariacarmela']]);
    expect(applySearch('mari'))->toBe([$ids['mariacarmela']]);
});

it('matches first then last name multi token', function () {
    $ids = seed([
        'mario' => ['Mario', 'Rossi'],
        'mariacarmela' => ['Mariacarmela', 'Rossi'],
    ]);

    expect(applySearch('mario rossi'))->toBe([$ids['mario']]);
});

it('does not match multi token across word boundary', function () {
    seed([
        'mariacarmela' => ['Mariacarmela', 'Rossi'],
    ]);

    expect(applySearch('maria rossi'))->toBe([]);
});

it('matches reversed last first multi token', function () {
    $ids = seed([
        'mario' => ['Mario', 'Rossi'],
    ]);

    expect(applySearch('rossi mario'))->toBe([$ids['mario']]);
});

it('matches composite italian multi-name', function () {
    $ids = seed([
        'mario' => ['Mario Giovanni', 'Rossi'],
    ]);

    expect(applySearch('mario giovanni rossi'))->toBe([$ids['mario']]);
    expect(applySearch('rossi mario giovanni'))->toBe([$ids['mario']]);
});

it('matches composite italian multi-cognome', function () {
    $ids = seed([
        'mario' => ['Mario', 'Rossi Bianchi'],
    ]);

    expect(applySearch('bianchi mario'))->toBe([$ids['mario']]);
    expect(applySearch('rossi bianchi mario'))->toBe([$ids['mario']]);
});

it('escapes percent wildcards in input', function () {
    $ids = seed([
        'sconto' => ['50%', 'Sconto'],
        'normal' => ['Mario', 'Rossi'],
    ]);

    expect(applySearch('50%'))->toBe([$ids['sconto']]);
});

it('escapes underscore wildcards in input', function () {
    $ids = seed([
        'underscore' => ['foo_bar', 'Baz'],
        'normal' => ['Mario', 'Rossi'],
    ]);

    expect(applySearch('foo_bar'))->toBe([$ids['underscore']]);
});

it('treats null columns as empty strings', function () {
    $marioNoLast = Person::create(['first_name' => 'Mario', 'last_name' => null])->id;

    expect(applySearch('mario'))->toContain($marioNoLast);
});

it('supports custom column names', function () {
    $person = Person::create([
        'given_name' => 'Maria',
        'family_name' => 'Bianchi',
    ]);

    expect(applySearch('maria', [
        'firstNameColumn' => 'given_name',
        'lastNameColumn' => 'family_name',
    ]))->toBe([$person->id]);
});

it('is unicode-safe', function () {
    $ids = seed([
        'maria' => ['María', 'Rossi'],
    ]);

    expect(applySearch('maría rossi'))->toBe([$ids['maria']]);
});
