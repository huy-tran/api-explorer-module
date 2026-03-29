<?php

namespace Modules\ApiExplorer\Data;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

readonly class EndpointData extends Data
{
    public function __construct(
        public string $method,
        public string $uri,
        public string $name,
        public string $module,
        public string $actionClass,
        public ?string $dtoClass = null,
        /** @var DataCollection<int, FieldSchema> */
        public DataCollection $fields = new DataCollection(FieldSchema::class, []),
        public array $middleware = [],
        public bool $requiresAuth = false,
    ) {}
}
