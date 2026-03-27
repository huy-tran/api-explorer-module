<?php

namespace Modules\ApiExplorer\Inspectors;

use Lorisleiva\Actions\Concerns\AsAction;

class ActionResolver
{
    public function resolve(array $routeAction): ?string
    {
        $uses = $routeAction['uses'] ?? null;

        if (! $uses) {
            return null;
        }

        if (is_string($uses)) {
            // Strip @method suffix if present (Controller@method style)
            return str_contains($uses, '@') ? explode('@', $uses)[0] : $uses;
        }

        return null;
    }

    public function isLaravelAction(string $class): bool
    {
        if (! class_exists($class)) {
            return false;
        }

        return in_array(AsAction::class, class_uses_recursive($class), true);
    }
}
