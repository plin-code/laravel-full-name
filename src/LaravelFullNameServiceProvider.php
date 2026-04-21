<?php

namespace PlinCode\LaravelFullName;

use PlinCode\LaravelFullName\Macros\EloquentBuilderMacros;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelFullNameServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package->name('laravel-full-name');
    }

    public function packageBooted(): void
    {
        EloquentBuilderMacros::register();
    }
}
