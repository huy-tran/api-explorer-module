<?php

namespace Modules\ApiExplorer\Data;

use Spatie\LaravelData\Data;

class EnvironmentData extends Data
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $baseUrl = null,
        /** @var array<string, string> */
        public readonly array $vars = [],
    ) {}
}
