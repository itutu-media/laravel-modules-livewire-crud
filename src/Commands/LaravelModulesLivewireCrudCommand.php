<?php

namespace ITUTUMedia\LaravelModulesLivewireCrud\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use ITUTUMedia\LaravelModulesLivewireCrud\Traits\ComponentParser;

class LaravelModulesLivewireCrudCommand extends Command
{
    use ComponentParser;

    protected $signature = 'module:make-crud {component} {model} {module} {--view=} {--force} {--inline} {--stub=} {--custom}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Livewire CRUD Component.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if (! $this->parser()) {
            return false;
        }

        if (! $this->checkClassNameValid()) {
            return false;
        }

        if (! $this->checkReservedClassName()) {
            return false;
        }

        $action = $this->createAction();
        $request = $this->createRequest();

        $indexClass = $this->createIndexClass();

        $table = $this->createTable();

        $formClass = $this->createFormClass();
        $formView = $this->createFormView();

        if ($action || $request || $indexClass || $table || $formClass || $formView) {
            $this->line("<options=bold,reverse;fg=green> COMPONENT CREATED </> 🤙\n");

            $action && $this->line("<options=bold;fg=green>ACTION:</> {$this->component->action->source}");
            $request && $this->line("<options=bold;fg=green>REQUEST:</> {$this->component->request->source}");

            $indexClass && $this->line("<options=bold;fg=green>INDEX CLASS:</> {$this->component->indexClass->source}");

            $table && $this->line("<options=bold;fg=green>TABLE:</> {$this->component->table->source}");

            $formClass && $this->line("<options=bold;fg=green>FORM CLASS:</> {$this->component->formClass->source}");
            $formView && $this->line("<options=bold;fg=green>FORM VIEW:</> {$this->component->formView->source}");
        }

        return false;
    }

    protected function createAction()
    {
        $actionFile = $this->component->action->file;

        if (File::exists($actionFile) && ! $this->isForce()) {
            $this->line("<options=bold,reverse;fg=red> WHOOPS-IE-TOOTLES </> 😳 \n");
            $this->line("<fg=red;options=bold>Action already exists:</> {$this->component->action->source}");

            return false;
        }

        $this->ensureDirectoryExists($actionFile);

        File::put($actionFile, $this->getActionContents());

        return $this->component->action;
    }

    protected function createRequest()
    {
        $requestFile = $this->component->request->file;

        if (File::exists($requestFile) && ! $this->isForce()) {
            $this->line("<options=bold,reverse;fg=red> WHOOPS-IE-TOOTLES </> 😳 \n");
            $this->line("<fg=red;options=bold>Request already exists:</> {$this->component->request->source}");

            return false;
        }

        $this->ensureDirectoryExists($requestFile);

        File::put($requestFile, $this->getRequestContents());

        return $this->component->request;
    }

    protected function createIndexClass()
    {
        $classFile = $this->component->indexClass->file;

        if (File::exists($classFile) && ! $this->isForce()) {
            $this->line("<options=bold,reverse;fg=red> WHOOPS-IE-TOOTLES </> 😳 \n");
            $this->line("<fg=red;options=bold>Class already exists:</> {$this->component->indexClass->source}");

            return false;
        }

        $this->ensureDirectoryExists($classFile);

        File::put($classFile, $this->getIndexClassContents());

        return $this->component->indexClass;
    }

    protected function createTable()
    {
        $tableFile = $this->component->table->file;

        if (File::exists($tableFile) && ! $this->isForce()) {
            $this->line("<options=bold,reverse;fg=red> WHOOPS-IE-TOOTLES </> 😳 \n");
            $this->line("<fg=red;options=bold>Table already exists:</> {$this->component->table->source}");

            return false;
        }

        $this->ensureDirectoryExists($tableFile);

        File::put($tableFile, $this->getTableContents());

        return $this->component->table;
    }

    protected function createFormClass()
    {
        $formFile = $this->component->formClass->file;

        if (File::exists($formFile) && ! $this->isForce()) {
            $this->line("<options=bold,reverse;fg=red> WHOOPS-IE-TOOTLES </> 😳 \n");
            $this->line("<fg=red;options=bold>Form class already exists:</> {$this->component->formClass->source}");

            return false;
        }

        $this->ensureDirectoryExists($formFile);

        File::put($formFile, $this->getFormClassContents());

        return $this->component->formClass;
    }

    protected function createFormView()
    {
        if ($this->isInline()) {
            return false;
        }

        $formFile = $this->component->formView->file;

        if (File::exists($formFile) && ! $this->isForce()) {
            $this->line("<fg=red;options=bold>Form view already exists:</> {$this->component->formView->source}");

            return false;
        }

        $this->ensureDirectoryExists($formFile);

        File::put($formFile, $this->getFormViewContents());

        return $this->component->formView;
    }
}
