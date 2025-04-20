<?php

namespace YSM\RepositoryPattern\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class MakeControllerCommand extends GeneratorCommand
{
    /**
     * @var string
     */
    protected $name = 'make:repository-controller';

    /**
     * @var string
     */
    protected $description = 'Create a new controller class (API or Web)';

    /**
     * @var string
     */
    protected $type = 'Controller';

    /**
     * @return string
     */
    protected function getStub()
    {
        $softDeletes = $this->option('soft-deletes');
        $type = $this->option('type') ?: 'api';

        if ($type === 'web') {
            return $softDeletes
                ? __DIR__ . '/../stubs/controller.web.soft-deletes.stub'
                : __DIR__ . '/../stubs/controller.web.stub';
        }

        return $softDeletes
            ? __DIR__ . '/../stubs/controller.api.soft-deletes.stub'
            : __DIR__ . '/../stubs/controller.api.stub';
    }

    /**
     * @param $rootNamespace
     *
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace): string
    {
        $type = $this->option('type') ?: 'api';
        $dir = $this->option('dir');

        if (is_null($dir) && $type === 'api') {
            return $rootNamespace . '\Http\Controllers\Api';
        } elseif (is_null($dir) && $type === 'web') {
            return $rootNamespace . '\Http\Controllers\Web';
        } else {
            $dir = trim($dir, '/');
            $dir = str_replace('/', '\\', $dir);
            $dir = str_replace('\\', '/', $dir);
            return $rootNamespace . '\Http\Controllers\\' . $dir;
        }

    }

    /**
     * @return string
     */
    protected function getNameInput(): string
    {
        return trim($this->argument('model')) . 'Controller';
    }

    /**
     * @param $stub
     * @param $name
     *
     * @return $this|\YSM\RepositoryPattern\Console\Commands\MakeControllerCommand
     */
    protected function replaceNamespace(&$stub, $name): MakeControllerCommand
    {
        $model = $this->argument('model');
        $modelNamespace = $this->laravel->getNamespace() . 'Models\\' . $model;
        $serviceNamespace = $model . 'Service';
        $modelVariable = lcfirst($model);
        $modelPlural = \Str::plural($modelVariable);
        $usesSoftDeletes = $this->option('soft-deletes') ? 'use YSM\\RepositoryPattern\\Traits\\InteractsServiceWithSoftDeletes;' : '';
        $withSoftDeletes = $this->option('soft-deletes') ? 'use InteractsServiceWithSoftDeletes;' : '';
        $stub = str_replace(
            [
                'DummyNamespace',
                'DummyModelNamespace',
                'DummyServiceName',
                'DummyModel',
                'DummyModelVariable',
                'VariableModelPlural',
                'DummyUsesSoftDeletes',
                'DummyWithSoftDeletes'
            ],
            [
                $this->getNamespace($name),
                $modelNamespace,
                $serviceNamespace,
                $model,
                $modelVariable,
                $modelPlural,
                $usesSoftDeletes,
                $withSoftDeletes
            ],
            $stub
        );

        return $this;
    }

    /**
     * @return array[]
     */
    protected function getArguments(): array
    {
        return [
            ['model', InputArgument::REQUIRED, 'The name of the model'],
        ];
    }

    /**
     * @return array[]
     */
    protected function getOptions(): array
    {
        return [
            ['type', 't', InputOption::VALUE_OPTIONAL, 'Controller type (api or web)', 'api'],
            ['dir', 'd', InputOption::VALUE_OPTIONAL, 'Controller directory'],
            ['soft-deletes', 's', InputOption::VALUE_NONE, 'Include soft delete methods'],
        ];
    }
}
