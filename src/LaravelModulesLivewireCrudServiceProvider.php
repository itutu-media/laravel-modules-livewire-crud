<?php

namespace ITUTUMedia\LaravelModulesLivewireCrud;

use ITUTUMedia\LaravelModulesLivewireCrud\Commands\LaravelModulesLivewireCrudCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelModulesLivewireCrudServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-modules-livewire-crud')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_laravel-modules-livewire-crud_table')
            ->hasCommand(LaravelModulesLivewireCrudCommand::class);
    }
}
