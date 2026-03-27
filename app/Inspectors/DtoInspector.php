<?php

namespace Modules\ApiExplorer\Inspectors;

use Illuminate\Database\Eloquent\Model;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionUnionType;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;
use Throwable;

class DtoInspector
{
    public function __construct(
        private readonly FieldTypeMapper $mapper,
    ) {}

    public function inspect(string $actionClass): array
    {
        try {
            $dtoClass = $this->resolveDtoClass($actionClass);
            if (! $dtoClass) {
                return [];
            }

            return $this->reflectProperties($dtoClass);
        } catch (Throwable) {
            return [];
        }
    }

    public function resolveDtoClass(string $actionClass): ?string
    {
        if (! class_exists($actionClass)) {
            return null;
        }

        $reflection = new ReflectionClass($actionClass);

        // Try asController() first, then handle()
        foreach (['asController', 'handle'] as $methodName) {
            if (! $reflection->hasMethod($methodName)) {
                continue;
            }

            $method = $reflection->getMethod($methodName);

            foreach ($method->getParameters() as $param) {
                $type = $param->getType();

                if (! $type instanceof ReflectionNamedType) {
                    continue;
                }

                $typeName = $type->getName();

                if (class_exists($typeName) && is_subclass_of($typeName, Data::class)) {
                    return $typeName;
                }
            }
        }

        return null;
    }

    private function reflectProperties(string $dtoClass): array
    {
        if (! class_exists($dtoClass)) {
            return [];
        }

        $reflection = new ReflectionClass($dtoClass);
        $constructor = $reflection->getConstructor();

        if (! $constructor) {
            return [];
        }

        return collect($constructor->getParameters())
            ->map(fn (ReflectionParameter $param) => $this->reflectParameter($param))
            ->filter()
            ->values()
            ->all();
    }

    private function reflectParameter(ReflectionParameter $param): ?array
    {
        $type = $param->getType();

        [$phpType, $nullable, $isOptional] = $this->resolveType($type);

        if (! $phpType) {
            return null;
        }

        // Skip Eloquent model parameters (route model binding, not form fields)
        if ($this->isEloquentModel($phpType)) {
            return null;
        }

        $mapped = $this->mapper->map($phpType, $nullable);

        $required = $this->isRequired($param, $nullable, $isOptional);
        $defaultValue = $param->isDefaultValueAvailable() ? $param->getDefaultValue() : null;
        $validationHint = $this->extractValidationHint($param);

        // Recurse into nested DTOs
        $nestedFields = [];
        if ($mapped['isNested'] && $mapped['nestedDtoClass']) {
            $nestedFields = $this->reflectProperties($mapped['nestedDtoClass']);
        }

        return [
            'name' => $param->getName(),
            'inputType' => $mapped['inputType'],
            'required' => $required,
            'nullable' => $nullable,
            'isOptional' => $isOptional,
            'defaultValue' => $defaultValue,
            'enumCases' => $mapped['enumCases'],
            'isArray' => $mapped['isArray'],
            'isNested' => $mapped['isNested'],
            'nestedDtoClass' => $mapped['nestedDtoClass'],
            'nestedFields' => $nestedFields,
            'validationHint' => $validationHint,
        ];
    }

    private function resolveType(mixed $type): array
    {
        if ($type instanceof ReflectionNamedType) {
            return [$type->getName(), $type->allowsNull(), false];
        }

        if ($type instanceof ReflectionUnionType) {
            $types = $type->getTypes();
            $isOptional = false;
            $nullable = false;
            $realType = null;

            foreach ($types as $t) {
                if (! $t instanceof ReflectionNamedType) {
                    continue;
                }

                $name = $t->getName();

                if ($name === Optional::class || $name === 'null') {
                    if ($name === Optional::class) {
                        $isOptional = true;
                    }
                    if ($name === 'null') {
                        $nullable = true;
                    }

                    continue;
                }

                $realType = $name;
            }

            return [$realType, $nullable, $isOptional];
        }

        return [null, false, false];
    }

    private function isRequired(ReflectionParameter $param, bool $nullable, bool $isOptional): bool
    {
        if ($isOptional) {
            return false;
        }

        // Check for Spatie #[Required] attribute
        foreach ($param->getAttributes() as $attr) {
            if ($attr->getName() === Required::class) {
                return true;
            }
        }

        // Non-nullable, non-optional = required
        return ! $nullable && ! $param->isDefaultValueAvailable();
    }

    private function extractValidationHint(ReflectionParameter $param): ?string
    {
        foreach ($param->getAttributes() as $attr) {
            $name = $attr->getName();
            // Extract useful hints from Spatie validation attributes
            if (str_contains($name, 'Validation\\')) {
                $shortName = class_basename($name);
                $args = $attr->getArguments();
                if (! empty($args)) {
                    return $shortName.': '.implode(', ', array_map('strval', $args[0] ?? $args));
                }

                return $shortName;
            }
        }

        return null;
    }

    private function isEloquentModel(string $type): bool
    {
        return class_exists($type) && is_subclass_of($type, Model::class);
    }
}
