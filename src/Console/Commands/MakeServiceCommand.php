<?php

namespace YSM\RepositoryPattern\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class MakeServiceCommand extends GeneratorCommand
{
    /**
     * @var string
     */
    protected $name = 'ysm:service';

    /**
     * @var string
     */
    protected $description = 'Create a new service class';

    /**
     * @var string
     */
    protected $type = '🎉 Service';

    /**
     * @return string
     */
    protected function getStub(): string
    {
        return $this->option('soft-deletes')
            ? __DIR__ . '/../stubs/service.soft-deletes.stub'
            : __DIR__ . '/../stubs/service.stub';
    }

    /**
     * @param $rawName
     *
     * @return bool
     */
    protected function alreadyExists($rawName): bool
    {
        // Always allow overwriting existing controllers
        return false;
    }


    /**
     * @return string
     */
    protected function getNameInput(): string
    {
        // Use the model argument and append 'Repository'
        return trim($this->argument('model')) . 'Service';
    }

    /**
     * @param $rootNamespace
     *
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace . '\Services';
    }

    /**
     * @param $stub
     * @param $name
     *
     * @return $this
     */
    protected function replaceNamespace(&$stub, $name): MakeServiceCommand
    {
        $model = $this->argument('model');
        $usesTrait = $this->option('soft-deletes') ? "use \\YSM\\RepositoryPattern\\Traits\\InteractsServiceWithSoftDeletes;\n" : '';
        $trait = $this->option('soft-deletes') ? 'use InteractsServiceWithSoftDeletes;' : '';

        $stub = str_replace(
            ['DummyNamespace', 'DummyModel', 'DummyModelVariable', 'DummyUsesTrait', 'DummyTrait'],
            [$this->getNamespace($name), $model, lcfirst($model), $usesTrait, $trait],
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
            ['soft-deletes', 's', InputOption::VALUE_NONE, 'Generate a repository with soft delete functionality'],
        ];
    }

}
