<?php

declare(strict_types=1);

use PlinCode\LaravelFullName\Support\FullNameMatcher;

it('trims leading and trailing whitespace', function (): void {
    expect(FullNameMatcher::normalize('  mario  '))->toBe('mario');
});

it('collapses internal whitespace runs to a single space', function (): void {
    expect(FullNameMatcher::normalize("mario   \t  rossi"))->toBe('mario rossi');
});

it('lowercases unicode characters', function (): void {
    expect(FullNameMatcher::normalize('MARÍA JOSÉ'))->toBe('maría josé');
});

it('returns empty string on only-whitespace input', function (): void {
    expect(FullNameMatcher::normalize('   '))->toBe('');
});

it('returns empty string on empty input', function (): void {
    expect(FullNameMatcher::normalize(''))->toBe('');
});

it('handles unicode whitespace', function (): void {
    expect(FullNameMatcher::normalize("mario\u{00A0}rossi"))->toBe('mario rossi');
});
