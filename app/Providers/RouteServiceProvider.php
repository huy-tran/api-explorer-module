<?php

namespace Modules\ApiExplorer\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    public function map(): void
    {
        if ($this->app->isProduction() || ! config('api-explorer.enabled', true)) {
            return;
        }

        Route::middleware('web')
            ->group(__DIR__.'/../../routes/web.php');
    }
}
