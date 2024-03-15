<?php

namespace ITUTUMedia\LaravelModulesLivewireCrud\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ITUTUMedia\LaravelModulesLivewireCrud\Support\Decomposer;

trait ComponentParser
{
    use CommandHelper;

    protected $component;

    protected $module;

    protected $model;

    protected $directories;

    protected function parser()
    {
        $checkDependencies = Decomposer::checkDependencies(
            $this->isCustomModule() ? ['livewire/livewire'] : null
        );

        if ($checkDependencies->type == 'error') {
            $this->line($checkDependencies->message);

            return false;
        }

        if (!$module = $this->getModule()) {
            return false;
        }

        $this->module = $module;

        $this->directories = collect(
            preg_split('/[.\/(\\\\)]+/', $this->argument('component'))
        )->map([Str::class, 'studly']);

        $this->model = $this->getModel();

        $this->component = $this->getComponent();

        return $this;
    }

    protected function getComponent()
    {
        $modelInfo = $this->getModelInfo();
        $actionInfo = $this->getActionInfo();
        $requestInfo = $this->getRequestInfo();

        $indexClassInfo = $this->getIndexClassInfo();

        $tableInfo = $this->getTableInfo();

        $formClassInfo = $this->getFormClassInfo();
        $formViewInfo = $this->getFormViewInfo();

        $stubInfo = $this->getStubInfo();

        return (object) [
            'model' => $modelInfo,
            'action' => $actionInfo,
            'request' => $requestInfo,
            'indexClass' => $indexClassInfo,
            'table' => $tableInfo,
            'formClass' => $formClassInfo,
            'formView' => $formViewInfo,
            'stub' => $stubInfo,
        ];
    }

    protected function getModelInfo()
    {
        $modelName = $this->getModelImport();
        $model = new $modelName();

        if ($model instanceof Model === false) {
            throw new \Exception('Invalid model given.');
        }

        $getFillable = [
            ...$model->getFillable(),
        ];

        return (object) [
            'name' => $this->getModelName(),
            'fillable' => $getFillable,
            'hidden' => $model->getHidden(),
            'table' => $model->getTable(),
        ];
    }

    protected function getActionInfo()
    {
        $modulePath = $this->getModulePath();

        $moduleLivewireNamespace = 'App\\Actions';

        $classDir = (string) Str::of($modulePath)
            ->append('/' . $moduleLivewireNamespace)
            ->replace(['\\'], '/');

        $classPath = $this->directories->implode('/');

        $namespace = $this->getNamespace($classPath, $moduleLivewireNamespace);

        $namespace = Str::replace('\\' . $this->directories->first(), '', $namespace);

        $className = $this->directories->first() . 'Action';

        $sourcePath = $this->getSourcePath($classDir . '/' . $className . '.php');

        return (object) [
            'dir' => $classDir,
            'path' => $classPath,
            'file' => $classDir . '/' . $className . '.php',
            'namespace' => $namespace,
            'name' => $className,
            'source' => $sourcePath,
        ];
    }

    protected function getRequestInfo()
    {
        $modulePath = $this->getModulePath();

        $moduleLivewireNamespace = 'App\\Http\\Requests';

        $classDir = (string) Str::of($modulePath)
            ->append('/' . $moduleLivewireNamespace)
            ->replace(['\\'], '/');

        $classPath = $this->directories->implode('/');

        $namespace = $this->getNamespace($classPath, $moduleLivewireNamespace);

        $namespace = Str::replace('\\' . $this->directories->first(), '', $namespace);

        $className = $this->directories->first() . 'Request';

        $sourcePath = $this->getSourcePath($classDir . '/' . $className . '.php');

        return (object) [
            'dir' => $classDir,
            'path' => $classPath,
            'file' => $classDir . '/' . $className . '.php',
            'namespace' => $namespace,
            'name' => $className,
            'source' => $sourcePath,
        ];
    }

    protected function getIndexClassInfo()
    {
        $modulePath = $this->getModulePath();

        $moduleLivewireNamespace = $this->getModuleLivewireNamespace();

        $classPath = $this->directories->implode('/');

        $classDir = (string) Str::of($modulePath)
            ->append('/' . $moduleLivewireNamespace . '/' . $classPath)
            ->replace(['\\'], '/');

        $namespace = $this->getNamespace($classPath . '/index');

        $className = 'Index';

        $sourcePath = $this->getSourcePath($classDir . '/' . $className . '.php');

        return (object) [
            'dir' => $classDir,
            'path' => $classPath,
            'file' => $classDir . '/' . $className . '.php',
            'namespace' => $namespace,
            'name' => $className,
            'source' => $sourcePath,
        ];
    }

    protected function getTableInfo()
    {
        $modulePath = $this->getModulePath();

        $moduleLivewireNamespace = $this->getModuleLivewireNamespace();

        $classPath = $this->directories->implode('/');

        $classDir = (string) Str::of($modulePath)
            ->append('/' . $moduleLivewireNamespace . '/' . $classPath)
            ->replace(['\\'], '/');

        $namespace = $this->getNamespace($classPath . '/Table');

        $className = 'Table';

        $sourcePath = $this->getSourcePath($classDir . '/' . $className . '.php');

        return (object) [
            'dir' => $classDir,
            'path' => $classPath,
            'file' => $classDir . '/' . $className . '.php',
            'namespace' => $namespace,
            'name' => $className,
            'source' => $sourcePath,
        ];
    }

    protected function getFormClassInfo()
    {
        $modulePath = $this->getModulePath();

        $moduleLivewireNamespace = $this->getModuleLivewireNamespace();

        $classPath = $this->directories->implode('/');

        $classDir = (string) Str::of($modulePath)
            ->append('/' . $moduleLivewireNamespace . '/' . $classPath)
            ->replace(['\\'], '/');

        $namespace = $this->getNamespace($classPath . '/Form');

        $className = 'Form';

        $sourcePath = $this->getSourcePath($classDir . '/' . $className . '.php');

        return (object) [
            'dir' => $classDir,
            'path' => $classPath,
            'file' => $classDir . '/' . $className . '.php',
            'namespace' => $namespace,
            'name' => $className,
            'source' => $sourcePath,
        ];
    }

    protected function getFormViewInfo()
    {
        $moduleLivewireViewDir = $this->getModuleLivewireViewDir();

        $path = $this->directories
            ->map([Str::class, 'kebab'])
            ->implode('/');

        if ($this->option('view')) {
            $path = strtr($this->option('view'), ['.' => '/']);
        }

        $sourcePath = $this->getSourcePath($moduleLivewireViewDir . '/' . $path . '/form.blade.php');

        return (object) [
            'dir' => $moduleLivewireViewDir,
            'path' => $path,
            'folder' => Str::after($moduleLivewireViewDir, 'views/'),
            'file' => $moduleLivewireViewDir . '/' . $path . '/form.blade.php',
            'name' => strtr($path, ['/' => '.']),
            'source' => $sourcePath,
        ];
    }

    protected function getStubInfo()
    {
        $defaultStubDir = __DIR__ . '/../../stubs/';

        $stubDir = File::isDirectory($publishedStubDir = base_path('stubs/modules-livewire-table/'))
            ? $publishedStubDir
            : $defaultStubDir;

        $classStub = File::exists($stubDir . 'index.stub')
            ? $stubDir . 'index.stub'
            : $defaultStubDir . 'index.stub';

        $tableStub = File::exists($stubDir . 'table.stub')
            ? $stubDir . 'table.stub'
            : $defaultStubDir . 'table.stub';

        $formClassStub = File::exists($stubDir . 'form.stub')
            ? $stubDir . 'form.stub'
            : $defaultStubDir . 'form.stub';
        $formViewStub = File::exists($stubDir . 'form.blade.stub')
            ? $stubDir . 'form.blade.stub'
            : $defaultStubDir . 'form.blade.stub';

        $actionStub = File::exists($stubDir . 'action.stub')
            ? $stubDir . 'action.stub'
            : $defaultStubDir . 'action.stub';

        $requestStub = File::exists($stubDir . 'request.stub')
            ? $stubDir . 'request.stub'
            : $defaultStubDir . 'request.stub';

        return (object) [
            'dir' => $stubDir,
            'indexClass' => $classStub,
            'table' => $tableStub,
            'formClass' => $formClassStub,
            'formView' => $formViewStub,
            'action' => $actionStub,
            'request' => $requestStub,
        ];
    }

    protected function getActionContents()
    {
        $getFillable = [
            ...$this->component->model->fillable,
        ];

        $data = [];

        foreach ($getFillable as $field) {
            if (in_array($field, $this->component->model->hidden)) {
                continue;
            }

            $data[] = '$this->data[' . "'" . $field . "'" . '] = $newData[' . "'" . $field . "'" . '];';
        }

        $data = implode("\n\t\t", $data);

        return preg_replace(
            ['/\[date\]/', '/\[namespace\]/', '/\[model_import\]/', '/\[class\]/', '/\[data\]/', '/\[model\]/'],
            [date('Y-m-d H:i:s') . ' ' . date_default_timezone_get(), $this->component->action->namespace, $this->getModelImport(), $this->component->action->name, $data, Str::lower($this->getModelName())],
            file_get_contents($this->component->stub->action),
        );
    }

    protected function getRequestContents()
    {
        $getFillable = [
            ...$this->component->model->fillable,
        ];

        $rules = [];

        foreach ($getFillable as $field) {
            if (in_array($field, $this->component->model->hidden)) {
                continue;
            }

            $rules[] = "'" . $field . "'" . ' => ' . "'" . 'required' . "'";
        }

        $rules = implode(",\n\t\t\t", $rules);

        return preg_replace(
            ['/\[date\]/', '/\[namespace\]/', '/\[model_import\]/', '/\[class\]/', '/\[data\]/', '/\[model\]/', '/\[rules\]/'],
            [date('Y-m-d H:i:s') . ' ' . date_default_timezone_get(), $this->component->request->namespace, $this->getModelImport(), $this->component->request->name, '', Str::lower($this->getModelName()), $rules],
            file_get_contents($this->component->stub->request),
        );
    }

    protected function getIndexClassContents()
    {
        $template = file_get_contents($this->component->stub->indexClass);

        if ($this->isInline()) {
            $template = preg_replace('/\[quote\]/', $this->getComponentQuote(), $template);
        }

        $component = Str::lower($this->directories->implode('.'));

        return preg_replace(
            ['/\[date\]/', '/\[namespace\]/', '/\[class\]/', '/\[module\]/', '/\[component\]/'],
            [date('Y-m-d H:i:s') . ' ' . date_default_timezone_get(), $this->component->indexClass->namespace, $this->component->indexClass->name, $this->getModuleLowerName(), $component],
            $template,
        );
    }

    protected function getTableContents()
    {
        $template = file_get_contents($this->component->stub->table);

        return preg_replace(
            ['/\[date\]/', '/\[namespace\]/', '/\[class\]/', '/\[model\]/', '/\[model_import\]/', '/\[columns\]/', '/\[action_import\]/', '/\[module\]/', '/\[model_low_case\]/', '/\[action\]/'],
            [date('Y-m-d H:i:s') . ' ' . date_default_timezone_get(), $this->component->table->namespace, $this->component->table->name, $this->getModelName(), $this->getModelImport(), $this->generateColumns($this->getModelImport()), $this->getActionImport(), $this->getModuleLowerName(), $this->component->formView->name, $this->component->action->name],
            $template,
        );
    }

    private function generateColumns(string $modelName): string
    {
        $model = new $modelName();

        if ($model instanceof Model === false) {
            throw new \Exception('Invalid model given.');
        }

        $getFillable = [
            ...[$model->getKeyName()],
            ...$model->getFillable(),
            ...['created_at', 'updated_at'],
        ];

        $columns = [];

        foreach ($getFillable as $field) {
            if (in_array($field, $model->getHidden())) {
                continue;
            }

            $title = Str::of($field)->replace('_', ' ')->ucfirst();

            if ($field === $model->getKeyName() && view()->exists('partials.table.action-button')) {
                $columns[] = 'Column::make("' . $title . '", "' . $field . '")' . "\n\t\t\t\t" . '->view("partials.table.action-button"),';
            } else {
                $columns[] = 'Column::make("' . $title . '", "' . $field . '")' . "\n\t\t\t\t" . '->sortable(),';
            }
        }

        return implode("\n\t\t\t", $columns);
    }

    protected function getFormClassContents()
    {
        $template = file_get_contents($this->component->stub->formClass);

        $getFillable = [
            ...$this->component->model->fillable,
        ];

        $fields = [];
        $resetFields = [];
        $setData = [];

        foreach ($getFillable as $field) {
            if (in_array($field, $this->component->model->hidden)) {
                continue;
            }

            $fields[] = '$' . $field;
            $resetFields[] = "'$field'";
            $setData[] = '$this->' . $field . ' = $this->state->' . $field . ';';
        }

        $fields = implode(',', $fields);
        $resetFields = implode(',', $resetFields);
        $setData = implode("\n\t\t", $setData);

        return preg_replace(
            ['/\[date\]/', '/\[namespace\]/', '/\[class\]/', '/\[module\]/', '/\[model\]/', '/\[model_import\]/', '/\[model_low_case\]/', '/\[action_import\]/', '/\[request_import\]/', '/\[title\]/', '/\[fields\]/', '/\[resetFields\]/', '/\[setData\]/', '/\[action\]/', '/\[request\]/'],
            [date('Y-m-d H:i:s') . ' ' . date_default_timezone_get(), $this->component->formClass->namespace, $this->component->formClass->name, $this->getModuleLowerName(), $this->getModelName(), $this->getModelImport(), Str::lower($this->getModelName()), $this->getActionImport(), $this->getRequestImport(), Str::title($this->component->formClass->name . ' Form'), $fields, $resetFields, $setData, $this->component->action->name, $this->component->request->name],
            $template,
        );
    }

    protected function getFormViewContents()
    {
        return preg_replace(
            ['/\[date\]/', '/\[forms\]/'],
            [date('Y-m-d H:i:s') . ' ' . date_default_timezone_get(), $this->getForms()],
            file_get_contents($this->component->stub->formView),
        );
    }

    private function getForms()
    {
        $getFillable = [
            ...$this->component->model->fillable,
        ];

        $forms = [];

        foreach ($getFillable as $field) {
            if (in_array($field, $this->component->model->hidden)) {
                continue;
            }

            $forms[] = '<x-input id="' . $field . '" label="' . Str::replace('_', ' ', Str::title($field)) . '" placeholder="' . Str::replace('_', ' ', Str::title($field)) . '" required :disabled="$disable" wire:model="' . Str::replace('_', ' ', Str::title($field)) . '" />';
        }

        return implode("\n\t\t", $forms);
    }
}
