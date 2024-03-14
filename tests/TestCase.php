<?php

namespace ITUTUMedia\LaravelModulesLivewireCrud\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Orchestra\Testbench\TestCase as Orchestra;
use ITUTUMedia\LaravelModulesLivewireCrud\LaravelModulesLivewireCrudServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'ITUTUMedia\\LaravelModulesLivewireCrud\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            LaravelModulesLivewireCrudServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');

        /*
        $migration = include __DIR__.'/../database/migrations/create_laravel-modules-livewire-crud_table.php.stub';
        $migration->up();
        */
    }
}
