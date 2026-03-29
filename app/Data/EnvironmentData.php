<?php

namespace Modules\ApiExplorer\Data;

use Spatie\LaravelData\Data;

readonly class EnvironmentData extends Data
{
    public function __construct(
        public string $name,
        public ?string $baseUrl = null,
        /** @var array<string, string> */
        public array $vars = [],
    ) {}
}
