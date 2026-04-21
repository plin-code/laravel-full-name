<?php

use PlinCode\LaravelFullName\Support\FullNameMatcher;

it('is idempotent on benign input', function () {
    expect(FullNameMatcher::escapeLike('mario rossi'))->toBe('mario rossi');
});

it('escapes percent signs', function () {
    expect(FullNameMatcher::escapeLike('50%'))->toBe('50!%');
});

it('escapes underscores', function () {
    expect(FullNameMatcher::escapeLike('foo_bar'))->toBe('foo!_bar');
});

it('escapes the escape character itself', function () {
    expect(FullNameMatcher::escapeLike('wow!'))->toBe('wow!!');
});

it('escapes the escape character before other wildcards', function () {
    expect(FullNameMatcher::escapeLike('%!_'))->toBe('!%!!!_');
});
