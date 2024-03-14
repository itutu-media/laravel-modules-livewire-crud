<?php

namespace ITUTUMedia\LaravelModulesLivewireCrud\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \ITUTUMedia\LaravelModulesLivewireCrud\LaravelModulesLivewireCrud
 */
class LaravelModulesLivewireCrud extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \ITUTUMedia\LaravelModulesLivewireCrud\LaravelModulesLivewireCrud::class;
    }
}
