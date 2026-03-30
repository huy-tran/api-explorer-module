<?php

namespace Modules\ApiExplorer\Inspectors;

use Lorisleiva\Actions\Concerns\AsAction;

class ActionResolver
{
    public function resolve(string $actionName): ?string
    {
        if ($actionName === '' || $actionName === 'Closure') {
            return null;
        }

        // Strip @method suffix if present (Controller@method style)
        return str_contains($actionName, '@') ? explode('@', $actionName)[0] : $actionName;
    }

    public function isLaravelAction(string $class): bool
    {
        if (! class_exists($class)) {
            return false;
        }

        return in_array(AsAction::class, class_uses_recursive($class), true);
    }
}
