<?php

namespace PlinCode\LaravelFullName;

use Filament\Tables\Columns\Column;
use PlinCode\LaravelFullName\Macros\EloquentBuilderMacros;
use PlinCode\LaravelFullName\Macros\FilamentColumnMacros;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelFullNameServiceProvider extends PackageServiceProvider
{
    #[\Override]
    public function configurePackage(Package $package): void
    {
        $package->name('laravel-full-name');
    }

    #[\Override]
    public function packageBooted(): void
    {
        EloquentBuilderMacros::register();

        if (class_exists(Column::class)) {
            FilamentColumnMacros::register();
        }
    }
}
