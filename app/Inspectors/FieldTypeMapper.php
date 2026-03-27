<?php

namespace Modules\ApiExplorer\Inspectors;

use BackedEnum;
use Carbon\Carbon;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use Illuminate\Http\UploadedFile;
use Spatie\LaravelData\Data;

class FieldTypeMapper
{
    public function map(string $phpType, bool $nullable): array
    {
        return match (true) {
            $phpType === 'bool' => $this->scalar('checkbox', $nullable),
            in_array($phpType, ['int', 'float']) => $this->scalar('number', $nullable),
            $phpType === 'string' => $this->scalar('text', $nullable),
            $phpType === 'array' => $this->arrayType(),
            $phpType === 'mixed' => $this->scalar('textarea', $nullable),
            $this->isDateTime($phpType) => $this->scalar('datetime-local', $nullable),
            $this->isFileUpload($phpType) => $this->scalar('file', $nullable),
            $this->isBackedEnum($phpType) => $this->enumType($phpType),
            $this->isSpatieData($phpType) => $this->nestedType($phpType),
            default => $this->scalar('text', $nullable),
        };
    }

    private function isDateTime(string $type): bool
    {
        return in_array($type, [Carbon::class, DateTime::class, DateTimeImmutable::class], true)
            || (class_exists($type) && is_a($type, DateTimeInterface::class, true));
    }

    private function isFileUpload(string $type): bool
    {
        return $type === UploadedFile::class
            || (class_exists($type) && is_a($type, UploadedFile::class, true));
    }

    private function isBackedEnum(string $type): bool
    {
        return class_exists($type) && is_a($type, BackedEnum::class, true);
    }

    private function isSpatieData(string $type): bool
    {
        return class_exists($type) && is_subclass_of($type, Data::class);
    }

    private function scalar(string $inputType, bool $nullable): array
    {
        return [
            'inputType' => $inputType,
            'isNested' => false,
            'nestedDtoClass' => null,
            'isArray' => false,
            'enumCases' => null,
        ];
    }

    private function arrayType(): array
    {
        return [
            'inputType' => 'text',
            'isNested' => false,
            'nestedDtoClass' => null,
            'isArray' => true,
            'enumCases' => null,
        ];
    }

    private function enumType(string $class): array
    {
        $cases = array_map(
            fn (BackedEnum $c) => $c->value,
            $class::cases()
        );

        return [
            'inputType' => 'select',
            'isNested' => false,
            'nestedDtoClass' => null,
            'isArray' => false,
            'enumCases' => $cases,
        ];
    }

    private function nestedType(string $class): array
    {
        return [
            'inputType' => 'nested',
            'isNested' => true,
            'nestedDtoClass' => $class,
            'isArray' => false,
            'enumCases' => null,
        ];
    }
}
