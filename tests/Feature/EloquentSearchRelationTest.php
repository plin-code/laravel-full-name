<?php

use PlinCode\LaravelFullName\Exceptions\UnsupportedRelationException;
use PlinCode\LaravelFullName\Support\FullNameMatcher;
use PlinCode\LaravelFullName\Support\FullNameOptions;
use PlinCode\LaravelFullName\Tests\Fixtures\Booking;
use PlinCode\LaravelFullName\Tests\Fixtures\Person;

function applyBookingSearch(string $input, ?string $relation = 'person'): array
{
    $query = Booking::query();
    FullNameMatcher::applySearch(
        $query,
        $input,
        new FullNameOptions(relation: $relation),
    );

    return $query->pluck('id')->all();
}

it('matches bookings via the BelongsTo person relation', function () {
    $mario = Person::create(['first_name' => 'Mario', 'last_name' => 'Rossi']);
    $luigi = Person::create(['first_name' => 'Luigi', 'last_name' => 'Verdi']);

    $marioBooking = Booking::create(['person_id' => $mario->id])->id;
    Booking::create(['person_id' => $luigi->id]);

    expect(applyBookingSearch('mario rossi'))->toBe([$marioBooking]);
});

it('does not match bookings with null person_id', function () {
    Booking::create(['person_id' => null, 'note' => 'orphan']);

    expect(applyBookingSearch('mario'))->toBe([]);
});

it('throws on missing relation', function () {
    expect(fn () => applyBookingSearch('mario', relation: 'totallyMadeUp'))
        ->toThrow(UnsupportedRelationException::class, "Relation 'totallyMadeUp' is not defined");
});

it('throws on non-BelongsTo relation', function () {
    expect(fn () => applyBookingSearch('mario', relation: 'items'))
        ->toThrow(UnsupportedRelationException::class, 'must be BelongsTo');
});
