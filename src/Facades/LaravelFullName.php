<?php

namespace PlinCode\LaravelFullName\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \PlinCode\LaravelFullName\LaravelFullName
 */
class LaravelFullName extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \PlinCode\LaravelFullName\LaravelFullName::class;
    }
}
