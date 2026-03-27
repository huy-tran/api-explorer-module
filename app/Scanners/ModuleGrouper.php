<?php

namespace Modules\ApiExplorer\Scanners;

class ModuleGrouper
{
    public function group(array $routes): array
    {
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
     * Resolves the hierarchy path for a route based on action namespace.
     * E.g., 'Modules\Client\Actions\Client\CreateAction' -> ['Client', 'Client']
     * E.g., 'Modules\Client\Actions\Document\CreateAction' -> ['Client', 'Document']
     * E.g., 'Modules\User\Actions\CreateAction' -> ['User']
     */
    private function resolveHierarchy(array $route): array
    {
        $uses = $route['action']['uses'] ?? null;

        // Try to extract from action class namespace
        if (is_string($uses) && str_contains($uses, 'Modules\\')) {
            $parts = explode('\\', $uses);

            // Structure: Modules\ModuleName\Actions\[ResourceName\]ActionClass
            if (count($parts) >= 4) {
                $module = $parts[1];

                // Check if there's a resource folder (e.g., Client, Document, etc.)
                // Actions are typically at: Modules\ModuleName\Actions\ResourceName\ActionClass
                // or Modules\ModuleName\Actions\ActionClass
                if ($parts[2] === 'Actions' && count($parts) > 4) {
                    // There's a resource folder between Actions and the class name
                    $resource = $parts[3];

                    return [$module, $resource];
                }

                // Only module level (no resource subfolder)
                return [$module];
            }
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

        // Store the route in the endpoints array at this level
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
