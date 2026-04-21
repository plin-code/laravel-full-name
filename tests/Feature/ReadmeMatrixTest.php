<?php

use PlinCode\LaravelFullName\Support\FullNameMatcher;
use PlinCode\LaravelFullName\Support\FullNameOptions;
use PlinCode\LaravelFullName\Tests\Fixtures\Person;

dataset('readme_matrix', [
    'mario first name' => ['mario', ['Mario', 'Rossi'], true],
    'rossi last name' => ['rossi', ['Mario', 'Rossi'], true],
    'mario rossi multi token' => ['mario rossi', ['Mario', 'Rossi'], true],
    'rossi mario reversed' => ['rossi mario', ['Mario', 'Rossi'], true],
    'maria substring on first' => ['maria', ['Marianna', 'Rossi'], true],
    'maria rossi not contiguous' => ['maria rossi', ['Marianna', 'Rossi'], false],
    'marianna rossi contiguous' => ['marianna rossi', ['Marianna', 'Rossi'], true],
    'composite italian ordered' => ['mario giovanni rossi', ['Mario Giovanni', 'Rossi'], true],
    'composite italian reversed' => ['rossi mario giovanni', ['Mario Giovanni', 'Rossi'], true],
    'multi cognome reversed' => ['bianchi mario', ['Mario', 'Rossi Bianchi'], true],
]);

it('matches the README behavior matrix', function (string $query, array $record, bool $expected): void {
    [$first, $last] = $record;
    $person = Person::create(['first_name' => $first, 'last_name' => $last]);

    $builder = Person::query();
    FullNameMatcher::applySearch($builder, $query, new FullNameOptions);
    $matched = $builder->where('id', $person->id)->exists();

    expect($matched)->toBe($expected);
})->with('readme_matrix');
