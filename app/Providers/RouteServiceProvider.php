<?php

namespace Modules\ApiExplorer\Providers;

use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Route;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class RouteServiceProvider extends ServiceProvider
{
    public function map(): void
    {
        if ($this->app->isProduction() || ! config('api-explorer.enabled', true)) {
            return;
        }

        Route::middleware(config('api-explorer.middleware', [
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
            ShareErrorsFromSession::class,
            ValidateCsrfToken::class,
        ]))->group(__DIR__.'/../../routes/web.php');
    }
}
