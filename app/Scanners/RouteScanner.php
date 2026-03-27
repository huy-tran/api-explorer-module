<?php

namespace Modules\ApiExplorer\Scanners;

use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Route as RouteFacade;
use Illuminate\Support\Str;

class RouteScanner
{
    public function scan(): array
    {
        return collect(RouteFacade::getRoutes()->getRoutes())
            ->filter(fn (Route $route) => ! $this->isExcluded($route) && ! $this->isWebRoute($route) && ! $this->isExplorerRoute($route))
            ->map(fn (Route $route) => $this->toArray($route))
            ->values()
            ->all();
    }

    private function isWebRoute(Route $route): bool
    {
        return in_array('web', $route->gatherMiddleware(), true);
    }

    private function isExplorerRoute(Route $route): bool
    {
        $prefix = config('api-explorer.route_prefix', 'dev/api-explorer');

        return str_starts_with($route->uri(), $prefix);
    }

    private function isExcluded(Route $route): bool
    {
        return collect(config('api-explorer.exclude_patterns', []))
            ->some(fn (string $pattern) => Str::of($route->uri())->isMatch('/'.$pattern.'/i'));
    }

    private function toArray(Route $route): array
    {
        $methods = array_filter(
            $route->methods(),
            fn (string $method) => $method !== 'HEAD'
        );

        return [
            'method' => implode('|', $methods),
            'uri' => '/'.$route->uri(),
            'name' => $route->getName() ?? '',
            'middleware' => $route->gatherMiddleware(),
            'action' => $route->getAction(),
        ];
    }
}
