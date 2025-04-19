<?php

namespace YSM\RepositoryPattern\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class MakeRepositoryCommand extends GeneratorCommand
{
    /**
     * @var string
     */
    protected $name = 'make:repository';

    /**
     * @var string
     */
    protected $description = 'Create a new repository class';

    /**
     * @var string
     */
    protected $type = 'Repository';

    /**
     * @return string
     */
    protected function getStub(): string
    {
        return $this->option('soft-deletes')
            ? __DIR__ . '/../stubs/repository.soft-deletes.stub'
            : __DIR__ . '/../stubs/repository.stub';
    }

    /**
     * @param $rootNamespace
     *
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace . '\Repositories';
    }

    /**
     * @param $stub
     * @param $name
     *
     * @return $this
     */
    protected function replaceNamespace(&$stub, $name): MakeRepositoryCommand
    {
        $model = $this->argument('model');
        $usesTrait = $this->option('soft-deletes') ? "use \\YSM\\RepositoryPattern\\Traits\\InteractsRepoWithSoftDeletes;\n" : '';
        $trait = $this->option('soft-deletes') ? 'use InteractsRepoWithSoftDeletes;' : '';

        $stub = str_replace(
            ['DummyNamespace', 'DummyModel', 'variableName', 'DummyUsesTrait', 'DummyTrait'],
            [$this->getNamespace($name), $model, lcfirst($model), $usesTrait, $trait],
            $stub
        );


        return $this;
    }

    /**
     * @return string
     */
    protected function getNameInput(): string
    {
        // Use the model argument and append 'Repository'
        return trim($this->argument('model')) . 'Repository';
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

    /**
     * @return array[]
     */
    protected function getArguments(): array
    {
        return [
            ['model', InputArgument::REQUIRED, 'The name of the model'],
        ];
    }
}
