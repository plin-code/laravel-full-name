<?php

declare(strict_types=1);

use PlinCode\LaravelFullName\Exceptions\InvalidSortDirectionException;
use PlinCode\LaravelFullName\Exceptions\UnsupportedRelationException;

it('formats unsupported relation type messages', function (): void {
    $exception = UnsupportedRelationException::forRelationType(
        relationName: 'items',
        modelClass: 'App\Models\Booking',
        actualType: 'HasMany',
    );

    expect($exception)->toBeInstanceOf(InvalidArgumentException::class)
        ->and($exception->getMessage())
        ->toContain("Relation 'items'")
        ->toContain('App\\Models\\Booking')
        ->toContain('BelongsTo')
        ->toContain('HasMany');
});

it('formats missing relation messages', function (): void {
    $exception = UnsupportedRelationException::forMissingRelation(
        relationName: 'nonexistent',
        modelClass: 'App\Models\Booking',
    );

    expect($exception)->toBeInstanceOf(InvalidArgumentException::class)
        ->and($exception->getMessage())
        ->toContain("Relation 'nonexistent'")
        ->toContain('App\\Models\\Booking')
        ->toContain('is not defined');
});

it('formats invalid sort direction messages', function (): void {
    $exception = InvalidSortDirectionException::fromDirection('sideways');

    expect($exception)->toBeInstanceOf(InvalidArgumentException::class)
        ->and($exception->getMessage())
        ->toContain("'asc'")
        ->toContain("'desc'")
        ->toContain("'sideways'");
});
