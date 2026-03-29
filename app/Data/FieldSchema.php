<?php

namespace Modules\ApiExplorer\Data;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

readonly class FieldSchema extends Data
{
    public function __construct(
        public string $name,
        public string $inputType,
        public bool $required,
        public bool $nullable,
        public bool $isOptional,
        public mixed $defaultValue = null,
        public ?array $enumCases = null,
        public bool $isArray = false,
        public bool $isNested = false,
        public ?string $nestedDtoClass = null,
        /** @var DataCollection<int, FieldSchema> */
        public DataCollection $nestedFields = new DataCollection(self::class, []),
        public ?string $validationHint = null,
        public bool $isFileField = false,
    ) {}
}
