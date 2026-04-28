<?php

declare(strict_types=1);

use PlinCode\LaravelFullName\Support\FullNameMatcher;
use PlinCode\LaravelFullName\Support\FullNameOptions;
use PlinCode\LaravelFullName\Tests\Fixtures\Account;
use PlinCode\LaravelFullName\Tests\Fixtures\Person;

/** @return array<int, mixed> */
function applyAccountSearch(string $input, ?string $relation = 'person'): array
{
    $query = Account::query();
    FullNameMatcher::applySearch(
        $query,
        $input,
        new FullNameOptions(relation: $relation),
    );

    return $query->pluck('id')->all();
}

it('matches accounts via the HasOne person relation', function (): void {
    $marioAccount = Account::create(['label' => 'mario'])->id;
    $luigiAccount = Account::create(['label' => 'luigi'])->id;

    Person::create(['account_id' => $marioAccount, 'first_name' => 'Mario', 'last_name' => 'Rossi']);
    Person::create(['account_id' => $luigiAccount, 'first_name' => 'Luigi', 'last_name' => 'Verdi']);

    expect(applyAccountSearch('mario rossi'))->toBe([$marioAccount]);
});

it('does not match accounts without a related person', function (): void {
    Account::create(['label' => 'orphan']);

    expect(applyAccountSearch('mario'))->toBe([]);
});

it('matches reversed last first via HasOne relation', function (): void {
    $accountId = Account::create(['label' => 'mario'])->id;
    Person::create(['account_id' => $accountId, 'first_name' => 'Mario', 'last_name' => 'Rossi']);

    expect(applyAccountSearch('rossi mario'))->toBe([$accountId]);
});
