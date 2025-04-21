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
    protected $name = 'ysm:repository';

    /**
     * @var string
     */
    protected $description = 'Create a new repository class';

    /**
     * @var string
     */
    protected $type = '🎉 Repository';

    public function handle()
    {


        $name = $this->qualifyClass($this->getNameInput());
        $path = $this->getPath($name);
        $existed = $this->files->exists($path);

        if ($existed && !$this->confirm("❗️ Repository [$name] already exists. Do you want to overwrite it?", true)) {
            $this->warn('❌ Command cancelled.');
            exit;
        }

        // Generate the repository
        parent::handle();

        // Customize success message
        $repositoryName = $this->argument('model') . 'Repository';
        $message = $existed
            ? "🎉 Repository [$repositoryName] overwritten Please don't forget to register it into config/repository-pattern.php."
            : "🎉 Repository [$repositoryName] created, Please don't forget to register it into config/repository-pattern.php.";

        $existed ? $this->warn($message) : $this->info($message);
    }

    /**
     * @return string
     */
    protected function getNameInput(): string
    {
        // Use the model argument and append 'Repository'
        return trim($this->argument('model')) . 'Repository';
    }


    protected function alreadyExists($rawName)
    {
        // Always allow overwriting existing repositories
        return false;
    }

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
