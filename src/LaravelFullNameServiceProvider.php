<?php

namespace PlinCode\LaravelFullName;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use PlinCode\LaravelFullName\Commands\LaravelFullNameCommand;

class LaravelFullNameServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-full-name')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_laravel_full_name_table')
            ->hasCommand(LaravelFullNameCommand::class);
    }
}
