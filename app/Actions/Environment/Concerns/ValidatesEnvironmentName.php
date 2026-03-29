<?php

namespace Modules\ApiExplorer\Actions\Environment\Concerns;

trait ValidatesEnvironmentName
{
    private function validateName(string $name): void
    {
        abort_if(
            basename($name) !== $name || ! preg_match('/^[a-zA-Z0-9_\-\[\] ]+$/', $name),
            400,
            'Invalid environment name.'
        );
    }
}
