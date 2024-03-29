<?php

namespace ITUTUMedia\LaravelModulesLivewireCrud\Traits;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

trait CommandHelper
{
    protected function isCustomModule()
    {
        return $this->option('custom') === true;
    }

    protected function isForce()
    {
        return $this->option('force') === true;
    }

    protected function isInline()
    {
        return $this->option('inline') === true;
    }

    protected function ensureDirectoryExists($path)
    {
        if (! File::isDirectory(dirname($path))) {
            File::makeDirectory(dirname($path), 0777, $recursive = true, $force = true);
        }
    }

    protected function getModule()
    {
        $moduleName = $this->argument('module');

        if ($this->isCustomModule()) {
            $module = config("modules-livewire.custom_modules.{$moduleName}");

            $path = $module['path'] ?? '';

            if (! $module || ! File::isDirectory($path)) {
                $this->line("<options=bold,reverse;fg=red> WHOOPS! </> 😳 \n");

                $path && $this->line("<fg=red;options=bold>The custom {$moduleName} module not found in this path - {$path}.</>");

                ! $path && $this->line("<fg=red;options=bold>The custom {$moduleName} module not found.</>");

                return null;
            }

            return $moduleName;
        }

        if (! $module = $this->laravel['modules']->find($moduleName)) {
            $this->line("<options=bold,reverse;fg=red> WHOOPS! </> 😳 \n");
            $this->line("<fg=red;options=bold>The {$moduleName} module not found.</>");

            return null;
        }

        return $module;
    }

    protected function getModuleName()
    {
        return $this->isCustomModule()
            ? $this->module
            : $this->module->getName();
    }

    protected function getModuleLowerName()
    {
        return $this->isCustomModule()
            ? config("modules-livewire.custom_modules.{$this->module}.name_lower", strtolower($this->module))
            : $this->module->getLowerName();
    }

    protected function getModulePath()
    {
        $path = $this->isCustomModule()
            ? config("modules-livewire.custom_modules.{$this->module}.path")
            : $this->module->getPath();

        return strtr($path, ['\\' => '/']);
    }

    protected function getModuleNamespace()
    {
        return $this->isCustomModule()
            ? config("modules-livewire.custom_modules.{$this->module}.module_namespace", $this->module)
            : config('modules.namespace', 'Modules');
    }

    protected function getModuleLivewireNamespace()
    {
        $moduleLivewireNamespace = config('modules-livewire.namespace', 'Http\\Livewire');

        if ($this->isCustomModule()) {
            return config("modules-livewire.custom_modules.{$this->module}.namespace", $moduleLivewireNamespace);
        }

        return $moduleLivewireNamespace;
    }

    protected function getModuleLivewireViewDir()
    {
        $moduleLivewireViewDir = config('modules-livewire.view', 'Resources/views/livewire');

        if ($this->isCustomModule()) {
            $moduleLivewireViewDir = config("modules-livewire.custom_modules.{$this->module}.view", $moduleLivewireViewDir);
        }

        return $this->getModulePath().'/'.$moduleLivewireViewDir;
    }

    protected function getModel()
    {
        return Str::studly($this->argument('model'));
    }

    protected function getModelName(): string
    {
        $explode = explode('\\', $this->getModelImport());

        return end($explode);
    }

    protected function getModelImport(): string
    {
        if (File::exists(app_path('Models/'.$this->model.'.php'))) {
            return 'App\Models\\'.$this->model;
        }

        if (File::exists(app_path($this->model.'.php'))) {
            return 'App\\'.$this->model;
        }

        return str_replace('/', '\\', $this->model);
    }

    protected function getActionImport(): string
    {
        return $this->component->action->namespace.'\\'.$this->component->action->name;
    }

    protected function getRequestImport(): string
    {
        return $this->component->request->namespace.'\\'.$this->component->request->name;
    }

    protected function getNamespace($classPath, $namespace = null)
    {
        $classPath = Str::contains($classPath, '/') ? '/'.$classPath : '';

        $namespace = $namespace ?: $this->getModuleLivewireNamespace();

        $prefix = $this->isCustomModule()
            ? $this->getModuleNamespace().'\\'.$namespace
            : $this->getModuleNamespace().'\\'.$this->module->getName().'\\'.$namespace;

        return (string) Str::of($classPath)
            ->beforeLast('/')
            ->prepend($prefix)
            ->replace(['/'], ['\\']);
    }

    protected function checkClassNameValid()
    {
        if (! $this->isClassNameValid($name = $this->component->indexClass->name)) {
            $this->line("<options=bold,reverse;fg=red> WHOOPS! </> 😳 \n");
            $this->line("<fg=red;options=bold>Class is invalid:</> {$name}");

            return false;
        }

        return true;
    }

    protected function checkReservedClassName()
    {
        if ($this->isReservedClassName($name = $this->component->indexClass->name)) {
            $this->line("<options=bold,reverse;fg=red> WHOOPS! </> 😳 \n");
            $this->line("<fg=red;options=bold>Class is reserved:</> {$name}");

            return false;
        }

        return true;
    }

    protected function isClassNameValid($name)
    {
        return (new \Livewire\Features\SupportConsoleCommands\Commands\MakeCommand())->isClassNameValid($name);
    }

    protected function isReservedClassName($name)
    {
        return (new \Livewire\Features\SupportConsoleCommands\Commands\MakeCommand())->isReservedClassName($name);
    }

    protected function getSourcePath($file, $path = null)
    {
        return Str::after($file, strtr(base_path($path), ['\\' => '/']).'/');
    }
}
