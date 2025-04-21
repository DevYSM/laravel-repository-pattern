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
    protected $name = 'ysm:controller';

    /**
     * @var string
     */
    protected $description = 'Create a new controller class (API or Web)';

    /**
     * @var string
     */
    protected $type = '🎉 Controller';


    /**
     * @return string
     */
    protected function getNameInput(): string
    {
        return trim($this->argument('model')) . 'Controller';
    }

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
            return $rootNamespace . '\Http\Controllers\\' . $dir;
        }
    }

    /**
     * @param $stub
     * @param $name
     *
     * @return $this
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
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
        $controllerNamespace = $this->getNamespace($name);
        $controllerClass = class_basename($name);

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
                $controllerNamespace,
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

        if ($this->option('with-routes')) {
            $this->generateRouteFile($model, $modelPlural, $controllerNamespace, $controllerClass);
            $this->updateRouteServiceProvider($model);
            $this->info('🎉 Routes created successfully!');
        }

        if ($this->option('with-rs')) {

            $this->call('ysm:repository', [
                'model' => $model,
                '--soft-deletes' => $this->option('soft-deletes'),
            ]);
            $this->call('ysm:service', [
                'model' => $model,
                '--soft-deletes' => $this->option('soft-deletes'),
            ]);
        }


        return $this;
    }

    /**
     * @param $model
     * @param $modelPlural
     * @param $controllerNamespace
     * @param $controllerClass
     *
     * @return void
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function generateRouteFile($model, $modelPlural, $controllerNamespace, $controllerClass)
    {
        $type = $this->option('type') ?: 'api';
        $softDeletes = $this->option('soft-deletes');
        $dir = str_replace('\\', '/', $this->getDir());
        if ($type === 'api') {
            $stub = $this->files->get(
                $softDeletes
                    ? __DIR__ . '/../stubs/routes.api.soft-deletes.stub'
                    : __DIR__ . '/../stubs/routes.api.stub'
            );
        } else {
            $stub = $this->files->get(
                $softDeletes
                    ? __DIR__ . '/../stubs/routes.soft-deletes.stub'
                    : __DIR__ . '/../stubs/routes.stub'
            );
        }


        $stub = str_replace(
            [
                'DummyModel',
                'VariableModelPlural',
                'DummyControllerNamespace',
                'DummyControllerClass',
            ],
            [
                $model,
                $modelPlural,
                $controllerNamespace,
                $controllerClass,
            ],
            $stub
        );


        $routeDir = base_path('routes/' . $dir);
        if (!$this->files->exists($routeDir)) {
            $this->files->makeDirectory($routeDir, 0755, true);
        }

        $model = strtolower($model);
        $model = \Str::plural($model);

        $this->files->put($routeDir . '/' . $model . '.php', $stub);
    }

    /**
     * @return string
     */
    protected function getDir()
    {
        $baseDir = $this->option('dir');
        $type = $this->option('type') ?: 'api';

        if (is_null($baseDir) && $type === 'api') {
            return 'api';
        } elseif (is_null($baseDir) && $type === 'web') {
            return 'web';
        } else {
            return strtolower($baseDir);
        }
    }

    /**
     * @param $model
     *
     * @return void
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function updateRouteServiceProvider($model)
    {
        $providerPath = app_path('Providers/RouteServiceProvider.php');
        $bootstrapPath = base_path('bootstrap/app.php');


        $model = strtolower($model);
        $model = \Str::plural($model);

        $dir = str_replace('\\', '/', $this->getDir());


        $routeInclude = $this->option('type') === 'api'
            ? "Route::prefix('$dir')
            ->middleware('api')
            ->group(base_path('routes/{$dir}/{$model}.php'));"
            : "Route::group(['prefix' => '{$dir}', 'name' => '{$dir}.'], base_path('routes/{$dir}/{$model}.php'));";

        // Check if RouteServiceProvider exists (Laravel 8.0–10.x)
        if ($this->files->exists($providerPath)) {
            $content = $this->files->get($providerPath);
            if (!str_contains($content, $routeInclude)) {
                // Try Laravel 8+ routes() method
                $insertPosition = strpos($content, '$this->routes(function () {');
                if ($insertPosition !== false) {
                    $insertPosition = strpos($content, '{', $insertPosition) + 1;
                    $content = substr_replace($content, "\n            $routeInclude\n", $insertPosition, 0);
                } else {
                    // Fallback to boot() method
                    $insertPosition = strpos($content, 'public function boot');
                    if ($insertPosition !== false) {
                        $insertPosition = strpos($content, '{', $insertPosition) + 1;
                        $content = substr_replace($content, "\n        $routeInclude\n", $insertPosition, 0);
                    }
                }
                $this->files->put($providerPath, $content);
            }
        } else {
            // Laravel 11+: Update bootstrap/app.php
            $content = $this->files->get($bootstrapPath);
            if (strpos($content, $routeInclude) === false) {
                // Add use statement if not present
                if (strpos($content, 'use Illuminate\Support\Facades\Route;') === false) {
                    $content = str_replace(
                        "<?php\n",
                        "<?php\n\nuse Illuminate\Support\Facades\Route;\n",
                        $content
                    );
                }
                // Add route registration
                $insertPosition = strpos($content, '->withRouting(');
                if ($insertPosition !== false) {
                    $insertPosition = strpos($content, ')', $insertPosition);
                    $content = substr_replace(
                        $content,
                        "   then: function () {\n        $routeInclude\n    }",
                        $insertPosition,
                        0
                    );
                    $this->files->put($bootstrapPath, $content);
                }
            }
        }

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
            ['with-routes', 'r', InputOption::VALUE_NONE, 'Generate and bind route file'],
            ['with-rs', 'rs', InputOption::VALUE_NONE, 'Generate repository and service'],
        ];
    }
}
