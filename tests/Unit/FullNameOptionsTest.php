<?php

use PlinCode\LaravelFullName\Support\FullNameOptions;

it('uses first_name and last_name as defaults', function () {
    $options = new FullNameOptions;

    expect($options->relation)->toBeNull()
        ->and($options->firstNameColumn)->toBe('first_name')
        ->and($options->lastNameColumn)->toBe('last_name');
});

it('accepts custom column names and relation', function () {
    $options = new FullNameOptions(
        relation: 'user',
        firstNameColumn: 'given_name',
        lastNameColumn: 'family_name',
    );

    expect($options->relation)->toBe('user')
        ->and($options->firstNameColumn)->toBe('given_name')
        ->and($options->lastNameColumn)->toBe('family_name');
});

it('clones without relation preserving columns', function () {
    $options = new FullNameOptions(
        relation: 'user',
        firstNameColumn: 'given_name',
        lastNameColumn: 'family_name',
    );

    $cloned = $options->withoutRelation();

    expect($cloned->relation)->toBeNull()
        ->and($cloned->firstNameColumn)->toBe('given_name')
        ->and($cloned->lastNameColumn)->toBe('family_name')
        ->and($cloned)->not->toBe($options);
});
