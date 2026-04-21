<?php

use PlinCode\LaravelFullName\Exceptions\InvalidSortDirectionException;
use PlinCode\LaravelFullName\Exceptions\UnsupportedRelationException;
use PlinCode\LaravelFullName\Tests\Fixtures\Booking;

it('throws on unsupported relation type via searchFullName macro', function () {
    expect(fn () => Booking::query()->searchFullName('mario', relation: 'items')->get())
        ->toThrow(UnsupportedRelationException::class);
});

it('throws on missing relation via searchFullName macro', function () {
    expect(fn () => Booking::query()->searchFullName('mario', relation: 'totallyMadeUp')->get())
        ->toThrow(UnsupportedRelationException::class);
});

it('throws on invalid direction via orderByFullName macro', function () {
    expect(fn () => Booking::query()->orderByFullName('sideways')->get())
        ->toThrow(InvalidSortDirectionException::class);
});

it('throws on unsupported relation type via orderByFullName macro', function () {
    expect(fn () => Booking::query()->orderByFullName('asc', relation: 'items')->get())
        ->toThrow(UnsupportedRelationException::class);
});
