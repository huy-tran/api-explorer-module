<?php

namespace Modules\ApiExplorer\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Modules\Base\Traits\ModuleRoutes;

class RouteServiceProvider extends ServiceProvider
{
    use ModuleRoutes {
        map as traitMap;
    }

    public function map(): void
    {
        if ($this->app->isProduction() || ! config('api-explorer.enabled', true)) {
            return;
        }

        $this->traitMap();
    }

    protected function getRouteDirectory(): string
    {
        return __DIR__.'/../../routes';
    }
}
