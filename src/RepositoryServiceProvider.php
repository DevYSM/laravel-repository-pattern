<?php

namespace YSM\RepositoryPattern;

use Illuminate\Support\ServiceProvider;
use YSM\RepositoryPattern\Console\Commands\MakeControllerCommand;
use YSM\RepositoryPattern\Console\Commands\MakeRepositoryCommand;
use YSM\RepositoryPattern\Console\Commands\MakeServiceCommand;
use YSM\RepositoryPattern\Contracts\RepositoryInterface;
use YSM\RepositoryPattern\Services\BaseService;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Bind the base service
        $this->app->bind(
            BaseService::class,
            function ($app) {
                return new BaseService(
                    $app->make(RepositoryInterface::class)
                );
            }
        );

        // Dynamic repository bindings based on config
        foreach (config('repository-pattern.bindings', []) as $model => $repository) {
            $this->app->bind(
                RepositoryInterface::class,
                function ($app) use ($model, $repository) {
                    return new $repository($app->make($model));
                }
            );

        }


//        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeRepositoryCommand::class,
                MakeServiceCommand::class,
                MakeControllerCommand::class,
            ]);
        }
    }

    public function boot()
    {
        // Publish configuration
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/Config/repository-pattern.php' => config_path('repository-pattern.php'),
            ], 'config');
        }
    }
}
