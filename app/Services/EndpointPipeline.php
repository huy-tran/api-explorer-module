<?php

namespace Modules\ApiExplorer\Services;

use Modules\ApiExplorer\Inspectors\ActionResolver;
use Modules\ApiExplorer\Inspectors\DtoInspector;
use Modules\ApiExplorer\Scanners\ModuleGrouper;
use Modules\ApiExplorer\Scanners\RouteScanner;

readonly class EndpointPipeline
{
    public function __construct(
        private RouteScanner $scanner,
        private ModuleGrouper $grouper,
        private ActionResolver $actionResolver,
        private DtoInspector $dtoInspector,
    ) {}

    public function run(): array
    {
        $routes = $this->scanner->scan();

        $grouped = $this->grouper->group($routes);

        // Transform flat endpoints array and preserve nested structure
        return $this->transformGroupedRoutes($grouped);
    }

    private function transformGroupedRoutes(array $grouped): array
    {
        $result = [];

        foreach ($grouped as $key => $value) {
            if (! is_array($value)) {
                continue;
            }

            $result[$key] = [];

            // Transform endpoints at this level if they exist
            if (isset($value['__endpoints']) && is_array($value['__endpoints'])) {
                $result[$key]['__endpoints'] = array_map(
                    fn ($route) => $this->transformRoute($route),
                    $value['__endpoints']
                );
            }

            // Process nested groups
            foreach ($value as $nestedKey => $nestedValue) {
                if ($nestedKey !== '__endpoints' && is_array($nestedValue)) {
                    $transformed = $this->transformGroupedRoutes([$nestedKey => $nestedValue]);
                    $result[$key][$nestedKey] = $transformed[$nestedKey];
                }
            }
        }

        return $result;
    }

    private function transformRoute(array $route): array
    {
        $actionClass = $this->actionResolver->resolve($route['action']);
        $dtoClass = $actionClass ? $this->dtoInspector->resolveDtoClass($actionClass) : null;
        $fields = $actionClass ? $this->dtoInspector->inspect($actionClass) : [];

        return [
            'method' => $route['method'],
            'uri' => $route['uri'],
            'name' => $route['name'],
            'actionClass' => $actionClass ?? '',
            'dtoClass' => $dtoClass,
            'fields' => $fields,
            'middleware' => $route['middleware'],
            'requiresAuth' => in_array('auth:sanctum', $route['middleware'], true),
        ];
    }
}
