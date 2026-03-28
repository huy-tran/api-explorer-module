<?php

namespace Modules\ApiExplorer\Data;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

class FieldSchema extends Data
{
    public function __construct(
        public readonly string $name,
        public readonly string $inputType,
        public readonly bool $required,
        public readonly bool $nullable,
        public readonly bool $isOptional,
        public readonly mixed $defaultValue = null,
        public readonly ?array $enumCases = null,
        public readonly bool $isArray = false,
        public readonly bool $isNested = false,
        public readonly ?string $nestedDtoClass = null,
        /** @var DataCollection<int, FieldSchema> */
        public readonly DataCollection $nestedFields = new DataCollection(self::class, []),
        public readonly ?string $validationHint = null,
        public readonly bool $isFileField = false,
    ) {}
}
