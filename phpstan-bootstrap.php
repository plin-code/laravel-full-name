<?php

// PHPStan bootstrap file — registers the package's macros so that
// Larastan's MacroMethodsClassReflectionExtension can resolve them at analysis time.

use Filament\Tables\Columns\TextColumn;
use PlinCode\LaravelFullName\Macros\EloquentBuilderMacros;
use PlinCode\LaravelFullName\Macros\FilamentColumnMacros;

EloquentBuilderMacros::register();

if (class_exists(TextColumn::class)) {
    FilamentColumnMacros::register();
}
