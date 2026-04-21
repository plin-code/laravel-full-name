<?php

declare(strict_types=1);

use PlinCode\LaravelFullName\Support\FullNameMatcher;

it('is idempotent on benign input', function (): void {
    expect(FullNameMatcher::escapeLike('mario rossi'))->toBe('mario rossi');
});

it('escapes percent signs', function (): void {
    expect(FullNameMatcher::escapeLike('50%'))->toBe('50!%');
});

it('escapes underscores', function (): void {
    expect(FullNameMatcher::escapeLike('foo_bar'))->toBe('foo!_bar');
});

it('escapes the escape character itself', function (): void {
    expect(FullNameMatcher::escapeLike('wow!'))->toBe('wow!!');
});

it('escapes the escape character before other wildcards', function (): void {
    expect(FullNameMatcher::escapeLike('%!_'))->toBe('!%!!!_');
});
