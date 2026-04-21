<?php

namespace PlinCode\LaravelFullName\Support;

final readonly class FullNameOptions
{
    public function __construct(
        public ?string $relation = null,
        public string $firstNameColumn = 'first_name',
        public string $lastNameColumn = 'last_name',
    ) {}

    public function withoutRelation(): self
    {
        return new self(
            relation: null,
            firstNameColumn: $this->firstNameColumn,
            lastNameColumn: $this->lastNameColumn,
        );
    }
}
