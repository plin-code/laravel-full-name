<?php

use PlinCode\LaravelFullName\Support\FullNameMatcher;
use PlinCode\LaravelFullName\Support\FullNameOptions;
use PlinCode\LaravelFullName\Tests\Fixtures\Person;

/**
 * @param  array<string, mixed>  $options
 * @return array<int, mixed>
 */
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

/**
 * @param  array<string, array{string, string}>  $names
 * @return array<string, mixed>
 */
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

it('returns the query unchanged on empty input', function (): void {
    $ids = seed(['mario' => ['Mario', 'Rossi']]);

    expect(applySearch(''))->toContain($ids['mario']);
    expect(applySearch('   '))->toContain($ids['mario']);
});

it('matches a single token against first_name', function (): void {
    $ids = seed([
        'mario' => ['Mario', 'Rossi'],
        'luigi' => ['Luigi', 'Verdi'],
    ]);

    expect(applySearch('mario'))->toBe([$ids['mario']]);
});

it('matches a single token against last_name', function (): void {
    $ids = seed([
        'mario' => ['Mario', 'Rossi'],
        'luigi' => ['Luigi', 'Verdi'],
    ]);

    expect(applySearch('verdi'))->toBe([$ids['luigi']]);
});

it('matches a single token as a substring', function (): void {
    $ids = seed([
        'marianna' => ['Marianna', 'Rossi'],
    ]);

    expect(applySearch('maria'))->toBe([$ids['marianna']]);
    expect(applySearch('mari'))->toBe([$ids['marianna']]);
});

it('matches first then last name multi token', function (): void {
    $ids = seed([
        'mario' => ['Mario', 'Rossi'],
        'marianna' => ['Marianna', 'Rossi'],
    ]);

    expect(applySearch('mario rossi'))->toBe([$ids['mario']]);
});

it('does not match multi token across word boundary', function (): void {
    seed([
        'marianna' => ['Marianna', 'Rossi'],
    ]);

    expect(applySearch('maria rossi'))->toBe([]);
});

it('matches reversed last first multi token', function (): void {
    $ids = seed([
        'mario' => ['Mario', 'Rossi'],
    ]);

    expect(applySearch('rossi mario'))->toBe([$ids['mario']]);
});

it('matches composite italian multi-name', function (): void {
    $ids = seed([
        'mario' => ['Mario Giovanni', 'Rossi'],
    ]);

    expect(applySearch('mario giovanni rossi'))->toBe([$ids['mario']]);
    expect(applySearch('rossi mario giovanni'))->toBe([$ids['mario']]);
});

it('matches composite italian multi-cognome', function (): void {
    $ids = seed([
        'mario' => ['Mario', 'Rossi Bianchi'],
    ]);

    expect(applySearch('bianchi mario'))->toBe([$ids['mario']]);
    expect(applySearch('rossi bianchi mario'))->toBe([$ids['mario']]);
});

it('escapes percent wildcards in input', function (): void {
    $ids = seed([
        'sconto' => ['50%', 'Sconto'],
        'normal' => ['Mario', 'Rossi'],
    ]);

    expect(applySearch('50%'))->toBe([$ids['sconto']]);
});

it('escapes underscore wildcards in input', function (): void {
    $ids = seed([
        'underscore' => ['foo_bar', 'Baz'],
        'normal' => ['Mario', 'Rossi'],
    ]);

    expect(applySearch('foo_bar'))->toBe([$ids['underscore']]);
});

it('treats null columns as empty strings', function (): void {
    $marioNoLast = Person::create(['first_name' => 'Mario', 'last_name' => null])->id;

    expect(applySearch('mario'))->toContain($marioNoLast);
});

it('supports custom column names', function (): void {
    $person = Person::create([
        'given_name' => 'Maria',
        'family_name' => 'Bianchi',
    ]);

    expect(applySearch('maria', [
        'firstNameColumn' => 'given_name',
        'lastNameColumn' => 'family_name',
    ]))->toBe([$person->id]);
});

it('is unicode-safe', function (): void {
    $ids = seed([
        'maria' => ['María', 'Rossi'],
    ]);

    expect(applySearch('maría rossi'))->toBe([$ids['maria']]);
});

it('exposes searchFullName as an Eloquent Builder macro', function (): void {
    $mario = Person::create(['first_name' => 'Mario', 'last_name' => 'Rossi'])->id;
    Person::create(['first_name' => 'Luigi', 'last_name' => 'Verdi']);

    $results = Person::query()->searchFullName('mario rossi')->pluck('id')->all();

    expect($results)->toBe([$mario]);
});
