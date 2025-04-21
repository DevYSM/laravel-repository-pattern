<?php

namespace YSM\RepositoryPattern\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
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
     * @param string $model
     *
     * @return void
     * @throws FileNotFoundException
     */
    protected function updateRouteServiceProvider(string $model): void
    {
        $providerPath = app_path('Providers/RouteServiceProvider.php');
        $bootstrapPath = base_path('bootstrap/app.php');

        $modelSlug = Str::plural(Str::lower($model));
        $dir = str_replace('\\', '/', $this->getDir());

        $isApi = $this->option('type') === 'api';

        $routeInclude = $isApi
            ? "Route::prefix('$dir')\n                ->middleware('api')\n                ->group(base_path('routes/{$dir}/{$modelSlug}.php'));"
            : "Route::group(['prefix' => '{$dir}', 'name' => '{$dir}.'], base_path('routes/{$dir}/{$modelSlug}.php'));";

        // Laravel 8–10: modify RouteServiceProvider
        if ($this->files->exists($providerPath)) {
            $content = $this->files->get($providerPath);

            if (!Str::contains($content, $routeInclude)) {
                if (Str::contains($content, '$this->routes(function ()')) {
                    $content = preg_replace_callback(
                        '/\$this->routes\(function\s*\(\)\s*{/',
                        function ($matches) use ($routeInclude) {
                            return $matches[0] . "\n            $routeInclude";
                        },
                        $content
                    );
                } elseif (Str::contains($content, 'public function boot')) {
                    $content = preg_replace_callback(
                        '/public function boot\([^\)]*\)\s*{/',
                        function ($matches) use ($routeInclude) {
                            return $matches[0] . "\n        $routeInclude";
                        },
                        $content
                    );
                }

                $this->files->put($providerPath, $content);
            }
        } // Laravel 11+: modify bootstrap/app.php
        elseif ($this->files->exists($bootstrapPath)) {
            $content = $this->files->get($bootstrapPath);

            if (!Str::contains($content, $routeInclude)) {
                // Ensure `use Route` is present
                if (!Str::contains($content, 'use Illuminate\Support\Facades\Route;')) {
                    $content = preg_replace(
                        '/<\?php\s*/',
                        "<?php\n\nuse Illuminate\Support\Facades\Route;\n",
                        $content,
                        1
                    );
                }

                // Insert into `->withRouting(` closure
                $content = preg_replace_callback(
                    '/->withRouting\s*\((.*?)\)/s',
                    function ($matches) use ($routeInclude) {
                        $existing = $matches[1];

                        // If a `then:` already exists, don't duplicate
                        if (Str::contains($existing, 'then:')) {
                            return $matches[0]; // skip modifying
                        }

                        $injected = $existing . ",\n    then: function () {\n        $routeInclude\n    }";
                        return str_replace($matches[1], $injected, $matches[0]);
                    },
                    $content
                );

                $this->files->put($bootstrapPath, $content);
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
