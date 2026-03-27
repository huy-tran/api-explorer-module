<?php

namespace Modules\ApiExplorer\Data;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

class EndpointData extends Data
{
    public function __construct(
        public readonly string $method,
        public readonly string $uri,
        public readonly string $name,
        public readonly string $module,
        public readonly string $actionClass,
        public readonly ?string $dtoClass = null,
        /** @var DataCollection<int, FieldSchema> */
        public readonly DataCollection $fields = new DataCollection(FieldSchema::class, []),
        public readonly array $middleware = [],
        public readonly bool $requiresAuth = false,
    ) {}
}
