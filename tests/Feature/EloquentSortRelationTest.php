<?php

use Illuminate\Database\QueryException;
use PlinCode\LaravelFullName\Exceptions\UnsupportedRelationException;
use PlinCode\LaravelFullName\Support\FullNameMatcher;
use PlinCode\LaravelFullName\Support\FullNameOptions;
use PlinCode\LaravelFullName\Tests\Fixtures\Booking;
use PlinCode\LaravelFullName\Tests\Fixtures\Person;

/** @return array<int, mixed> */
function sortedBookingIds(string $direction = 'asc'): array
{
    $query = Booking::query();
    FullNameMatcher::applySort(
        $query,
        $direction,
        new FullNameOptions(relation: 'person'),
    );

    return $query->pluck('id')->all();
}

it('orders bookings by related person full name ascending', function () {
    $bianchi = Person::create(['first_name' => 'Anna', 'last_name' => 'Bianchi']);
    $azziAnna = Person::create(['first_name' => 'Anna', 'last_name' => 'Azzi']);
    $azziGiulia = Person::create(['first_name' => 'Giulia', 'last_name' => 'Azzi']);

    $b = Booking::create(['person_id' => $bianchi->id])->id;
    $a1 = Booking::create(['person_id' => $azziGiulia->id])->id;
    $a2 = Booking::create(['person_id' => $azziAnna->id])->id;

    expect(sortedBookingIds('asc'))->toBe([$a2, $a1, $b]);
});

it('orders bookings descending', function () {
    $bianchi = Person::create(['first_name' => 'Anna', 'last_name' => 'Bianchi']);
    $azzi = Person::create(['first_name' => 'Anna', 'last_name' => 'Azzi']);

    $b = Booking::create(['person_id' => $bianchi->id])->id;
    $a = Booking::create(['person_id' => $azzi->id])->id;

    expect(sortedBookingIds('desc'))->toBe([$b, $a]);
});

it('does not duplicate rows on the main table', function () {
    $a = Person::create(['first_name' => 'Anna', 'last_name' => 'Azzi']);
    $b = Person::create(['first_name' => 'Giulia', 'last_name' => 'Bianchi']);

    Booking::create(['person_id' => $a->id]);
    Booking::create(['person_id' => $b->id]);

    expect(sortedBookingIds())->toHaveCount(2);
});

it('excludes soft-deleted related records', function () {
    $alive = Person::create(['first_name' => 'Anna', 'last_name' => 'Azzi']);
    $deleted = Person::create(['first_name' => 'Carlo', 'last_name' => 'Bianchi']);
    $deleted->delete();

    $aliveBooking = Booking::create(['person_id' => $alive->id])->id;
    Booking::create(['person_id' => $deleted->id]);

    expect(sortedBookingIds())->toBe([$aliveBooking]);
});

it('is idempotent on repeated calls', function () {
    $a = Person::create(['first_name' => 'Anna', 'last_name' => 'Azzi']);
    $b = Person::create(['first_name' => 'Giulia', 'last_name' => 'Bianchi']);

    Booking::create(['person_id' => $a->id]);
    Booking::create(['person_id' => $b->id]);

    $query = Booking::query();
    FullNameMatcher::applySort($query, 'asc', new FullNameOptions(relation: 'person'));
    FullNameMatcher::applySort($query, 'asc', new FullNameOptions(relation: 'person'));

    expect($query->get())->toHaveCount(2);
});

it('throws on non-BelongsTo relation for sort', function () {
    $query = Booking::query();

    expect(fn () => FullNameMatcher::applySort(
        $query,
        'asc',
        new FullNameOptions(relation: 'items'),
    ))->toThrow(UnsupportedRelationException::class, 'must be BelongsTo');
});

it('throws on missing relation for sort', function () {
    $query = Booking::query();

    expect(fn () => FullNameMatcher::applySort(
        $query,
        'asc',
        new FullNameOptions(relation: 'totallyMadeUp'),
    ))->toThrow(UnsupportedRelationException::class, 'is not defined');
});

it('skips duplicate joinSub when orderByFullName is called twice', function () {
    $a = Person::create(['first_name' => 'Anna', 'last_name' => 'Azzi']);
    $b = Person::create(['first_name' => 'Giulia', 'last_name' => 'Bianchi']);

    Booking::create(['person_id' => $a->id]);
    Booking::create(['person_id' => $b->id]);

    $query = Booking::query();
    FullNameMatcher::applySort($query, 'asc', new FullNameOptions(relation: 'person'));
    FullNameMatcher::applySort($query, 'asc', new FullNameOptions(relation: 'person'));

    $joins = $query->getQuery()->joins ?? [];

    expect($joins)->toHaveCount(1);
});

it('raises a clear error when unqualified select collides with joined columns', function () {
    Person::create(['first_name' => 'Anna', 'last_name' => 'Azzi']);
    Booking::create(['person_id' => 1]);

    $query = Booking::query()->select(['id']);

    expect(function () use ($query) {
        FullNameMatcher::applySort($query, 'asc', new FullNameOptions(relation: 'person'));
        $query->get();
    })->toThrow(QueryException::class);
});

it('preserves pre-existing select columns on the main query', function () {
    $alice = Person::create(['first_name' => 'Alice', 'last_name' => 'Smith']);
    $bob = Person::create(['first_name' => 'Bob', 'last_name' => 'Jones']);

    Booking::create(['person_id' => $bob->id, 'note' => 'bob-note']);
    Booking::create(['person_id' => $alice->id, 'note' => 'alice-note']);

    $query = Booking::query()->select(['test_bookings.id', 'test_bookings.note']);
    FullNameMatcher::applySort($query, 'asc', new FullNameOptions(relation: 'person'));

    $rows = $query->get();

    // Order by last_name asc: Jones (Bob) before Smith (Alice)
    expect($rows->pluck('note')->all())->toBe(['bob-note', 'alice-note']);
    // Result only carries the originally selected columns plus related columns via join (no leakage of test_bookings.person_id or timestamps into the attribute list)
    expect($rows->first()->getAttributes())->toHaveKey('note');
});
