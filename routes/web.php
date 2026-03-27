<?php

use Illuminate\Support\Facades\Route;
use Modules\ApiExplorer\Actions\ApiExplorerIndex;
use Modules\ApiExplorer\Actions\Environment\DeleteEnvironment;
use Modules\ApiExplorer\Actions\Environment\GetEnvironment;
use Modules\ApiExplorer\Actions\Environment\ListEnvironments;
use Modules\ApiExplorer\Actions\Environment\SaveEnvironment;
use Modules\ApiExplorer\Actions\PurgeApiExplorerCache;

Route::prefix(config('api-explorer.route_prefix', 'dev/api-explorer'))
    ->name('api-explorer.')
    ->group(function (): void {
        Route::get('/', ApiExplorerIndex::class)->name('index');
        Route::delete('/cache', PurgeApiExplorerCache::class)->name('cache.purge');

        Route::prefix('/environments')->name('environments.')->group(function (): void {
            Route::get('/', ListEnvironments::class)->name('index');
            Route::get('/{name}', GetEnvironment::class)->name('show');
            Route::post('/', SaveEnvironment::class)->name('store');
            Route::put('/{name}', SaveEnvironment::class)->name('update');
            Route::delete('/{name}', DeleteEnvironment::class)->name('destroy');
        });
    });
