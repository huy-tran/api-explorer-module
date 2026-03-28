<?php

namespace Modules\ApiExplorer\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\ApiExplorer\Console\ClearCacheCommand;
use Modules\ApiExplorer\Console\ScanCommand;

class ApiExplorerServiceProvider extends ServiceProvider
{
    protected string $moduleNameLower = 'api-explorer';

    private function basePath(string $path = ''): string
    {
        return dirname(__DIR__, 2).($path ? '/'.$path : '');
    }

    public function boot(): void
    {
        if ($this->app->isProduction()) {
            return;
        }

        if (! config($this->moduleNameLower.'.enabled', true)) {
            return;
        }

        $this->registerCommands();
        $this->registerConfig();
        $this->registerViews();
    }

    public function register(): void
    {
        $this->mergeConfigFrom(
            $this->basePath('config/config.php'),
            $this->moduleNameLower
        );

        $this->app->register(EventServiceProvider::class);
        $this->app->register(RouteServiceProvider::class);
    }

    protected function registerCommands(): void
    {
        $this->commands([
            ClearCacheCommand::class,
            ScanCommand::class,
        ]);
    }

    protected function registerConfig(): void
    {
        $this->publishes([
            $this->basePath('config/config.php') => config_path($this->moduleNameLower.'.php'),
        ], 'config');

        $this->publishes([
            $this->basePath('resources/views') => resource_path('views/vendor/'.$this->moduleNameLower),
        ], 'views');
    }

    public function registerViews(): void
    {
        $sourcePath = $this->basePath('resources/views');
        $this->loadViewsFrom($sourcePath, $this->moduleNameLower);
    }

    public function provides(): array
    {
        return [];
    }
}
