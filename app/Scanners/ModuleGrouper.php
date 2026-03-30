<?php

namespace Modules\ApiExplorer\Scanners;

use Illuminate\Support\Facades\File;
use Symfony\Component\Finder\SplFileInfo;

class ModuleGrouper
{
    /** @var array<string, array<string>> route name => [Module, Subgroup?] */
    private array $routeMap = [];

    public function group(array $routes): array
    {
        $this->routeMap = $this->buildRouteMap();

        $grouped = [];

        foreach ($routes as $route) {
            $hierarchy = $this->resolveHierarchy($route);
            $this->nestRoute($grouped, $hierarchy, $route);
        }

        ksort($grouped);
        $this->sortNested($grouped);

        return $grouped;
    }

    /**
     * Scans module route directories to build a mapping of route name to hierarchy.
     *
     * @return array<string, array<string>>
     */
    private function buildRouteMap(): array
    {
        $modulesPath = base_path('modules');

        if (! is_dir($modulesPath)) {
            return [];
        }

        $map = [];

        foreach (File::directories($modulesPath) as $moduleDir) {
            $module = basename($moduleDir);
            $routesDir = $moduleDir.'/routes';

            if (! is_dir($routesDir)) {
                continue;
            }

            foreach (File::allFiles($routesDir) as $file) {
                /** @var SplFileInfo $file */
                $filename = $file->getFilename();

                // Skip non-route files
                if ($filename === '.gitkeep' || $filename === 'web.php') {
                    continue;
                }

                $parts = explode('.', $filename);

                if (count($parts) < 3) {
                    continue;
                }

                $name = $parts[0];
                $version = $parts[1];
                $routeName = $name.'_'.$version;

                // Determine subgroup from the directory relative to routes/
                $relativePath = $file->getRelativePath();
                $hierarchy = [$module];

                if ($relativePath !== '') {
                    $hierarchy[] = $relativePath;
                }

                $map[$routeName] = $hierarchy;
            }
        }

        return $map;
    }

    /**
     * Resolves the hierarchy for a route using the filesystem-based route map.
     */
    private function resolveHierarchy(array $route): array
    {
        $name = $route['name'] ?? '';

        if ($name !== '' && isset($this->routeMap[$name])) {
            return $this->routeMap[$name];
        }

        return ['Unknown'];
    }

    /**
     * Nests a route into the grouped array structure.
     */
    private function nestRoute(array &$grouped, array $hierarchy, array $route): void
    {
        $current = &$grouped;

        foreach ($hierarchy as $segment) {
            if (! isset($current[$segment])) {
                $current[$segment] = [];
            }
            $current = &$current[$segment];
        }

        if (! isset($current['__endpoints'])) {
            $current['__endpoints'] = [];
        }
        $current['__endpoints'][] = $route;
    }

    /**
     * Recursively sort nested groups.
     */
    private function sortNested(array &$array): void
    {
        ksort($array);
        foreach ($array as &$value) {
            if (is_array($value) && ! isset($value[0])) {
                $this->sortNested($value);
            }
        }
    }
}
