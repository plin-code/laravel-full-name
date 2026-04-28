<?php

declare(strict_types=1);

use PlinCode\LaravelFullName\Support\FullNameMatcher;
use PlinCode\LaravelFullName\Support\FullNameOptions;
use PlinCode\LaravelFullName\Tests\Fixtures\Account;
use PlinCode\LaravelFullName\Tests\Fixtures\Person;

/** @return array<int, mixed> */
function sortedAccountIds(string $direction = 'asc'): array
{
    $query = Account::query();
    FullNameMatcher::applySort(
        $query,
        $direction,
        new FullNameOptions(relation: 'person'),
    );

    return $query->pluck('id')->all();
}

it('orders accounts by related person full name ascending via HasOne', function (): void {
    $bianchi = Account::create(['label' => 'bianchi'])->id;
    $azziGiulia = Account::create(['label' => 'azzi-giulia'])->id;
    $azziAnna = Account::create(['label' => 'azzi-anna'])->id;

    Person::create(['account_id' => $bianchi, 'first_name' => 'Anna', 'last_name' => 'Bianchi']);
    Person::create(['account_id' => $azziGiulia, 'first_name' => 'Giulia', 'last_name' => 'Azzi']);
    Person::create(['account_id' => $azziAnna, 'first_name' => 'Anna', 'last_name' => 'Azzi']);

    expect(sortedAccountIds('asc'))->toBe([$azziAnna, $azziGiulia, $bianchi]);
});

it('orders accounts descending via HasOne', function (): void {
    $bianchi = Account::create(['label' => 'b'])->id;
    $azzi = Account::create(['label' => 'a'])->id;

    Person::create(['account_id' => $bianchi, 'first_name' => 'Anna', 'last_name' => 'Bianchi']);
    Person::create(['account_id' => $azzi, 'first_name' => 'Anna', 'last_name' => 'Azzi']);

    expect(sortedAccountIds('desc'))->toBe([$bianchi, $azzi]);
});

it('excludes soft-deleted related records via HasOne', function (): void {
    $aliveAccount = Account::create(['label' => 'alive'])->id;
    $deletedAccount = Account::create(['label' => 'deleted'])->id;

    Person::create(['account_id' => $aliveAccount, 'first_name' => 'Anna', 'last_name' => 'Azzi']);
    $deleted = Person::create(['account_id' => $deletedAccount, 'first_name' => 'Carlo', 'last_name' => 'Bianchi']);
    $deleted->delete();

    expect(sortedAccountIds())->toBe([$aliveAccount]);
});

it('skips duplicate joinSub on repeated HasOne sort calls', function (): void {
    $a = Account::create(['label' => 'a'])->id;
    $b = Account::create(['label' => 'b'])->id;
    Person::create(['account_id' => $a, 'first_name' => 'Anna', 'last_name' => 'Azzi']);
    Person::create(['account_id' => $b, 'first_name' => 'Giulia', 'last_name' => 'Bianchi']);

    $query = Account::query();
    FullNameMatcher::applySort($query, 'asc', new FullNameOptions(relation: 'person'));
    FullNameMatcher::applySort($query, 'asc', new FullNameOptions(relation: 'person'));

    $joins = $query->getQuery()->joins ?? [];

    expect($joins)->toHaveCount(1);
});

it('does not duplicate accounts when sorting via HasOne', function (): void {
    $a = Account::create(['label' => 'a'])->id;
    $b = Account::create(['label' => 'b'])->id;

    Person::create(['account_id' => $a, 'first_name' => 'Anna', 'last_name' => 'Azzi']);
    Person::create(['account_id' => $b, 'first_name' => 'Giulia', 'last_name' => 'Bianchi']);

    expect(sortedAccountIds())->toHaveCount(2);
});
