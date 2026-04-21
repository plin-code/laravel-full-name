<?php

use PlinCode\LaravelFullName\Support\FullNameMatcher;

it('trims leading and trailing whitespace', function () {
    expect(FullNameMatcher::normalize('  mario  '))->toBe('mario');
});

it('collapses internal whitespace runs to a single space', function () {
    expect(FullNameMatcher::normalize("mario   \t  rossi"))->toBe('mario rossi');
});

it('lowercases unicode characters', function () {
    expect(FullNameMatcher::normalize('MARÍA JOSÉ'))->toBe('maría josé');
});

it('returns empty string on only-whitespace input', function () {
    expect(FullNameMatcher::normalize('   '))->toBe('');
});

it('returns empty string on empty input', function () {
    expect(FullNameMatcher::normalize(''))->toBe('');
});

it('handles unicode whitespace', function () {
    expect(FullNameMatcher::normalize("mario\u{00A0}rossi"))->toBe('mario rossi');
});
