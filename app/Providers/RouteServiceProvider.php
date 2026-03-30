<?php

namespace Modules\ApiExplorer\Providers;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /** @var list<class-string> */
    protected array $defaultMiddleware = [
        \Illuminate\Cookie\Middleware\EncryptCookies::class,
        \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
        \Illuminate\Session\Middleware\StartSession::class,
        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
        \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
    ];

    public function map(): void
    {
        if ($this->app->isProduction() || ! config('api-explorer.enabled', true)) {
            return;
        }

        Route::middleware($this->resolveMiddleware())
            ->withoutMiddleware($this->resolveExcludedMiddleware())
            ->group(__DIR__.'/../../routes/web.php');
    }

    /** @return list<class-string> */
    protected function resolveMiddleware(): array
    {
        if ($middleware = config('api-explorer.middleware')) {
            return $middleware;
        }

        return array_values(array_filter(
            $this->defaultMiddleware,
            fn (string $class): bool => class_exists($class),
        ));
    }

    /** @return list<class-string> */
    protected function resolveExcludedMiddleware(): array
    {
        return array_values(array_unique(array_merge(
            $this->getGlobalMiddleware(),
            config('api-explorer.excluded_middleware', []),
        )));
    }

    /** @return list<class-string> */
    protected function getGlobalMiddleware(): array
    {
        $kernel = $this->app->make(Kernel::class);

        if (method_exists($kernel, 'getGlobalMiddleware')) {
            return $kernel->getGlobalMiddleware();
        }

        // Laravel 10: $middleware property is protected, use reflection
        $reflection = new \ReflectionProperty($kernel, 'middleware');

        return $reflection->getValue($kernel);
    }
}
