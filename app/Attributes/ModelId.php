<?php

namespace Modules\ApiExplorer\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER | Attribute::TARGET_PROPERTY)]
class ModelId
{
    public function __construct(
        public readonly string $key,
        public readonly string $inputType = 'number',
    ) {}
}
