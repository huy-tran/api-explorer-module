<?php

use Illuminate\Database\Eloquent\Model;
use Modules\ApiExplorer\Attributes\ModelId;
use Modules\ApiExplorer\Inspectors\DtoInspector;
use Modules\ApiExplorer\Inspectors\FieldTypeMapper;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;
use Tests\TestCase;

uses(TestCase::class);

// ---------------------------------------------------------------------------
// Fixtures
// ---------------------------------------------------------------------------

class FakeModel extends Model
{
    protected $table = 'fake_models';
}

class DtoWithPlainModel extends Data
{
    public function __construct(
        public FakeModel $model,
        public string $name,
    ) {}
}

class DtoWithModelId extends Data
{
    public function __construct(
        #[Required]
        #[ModelId('fakeModelId')]
        public FakeModel $model,
        public string $name,
    ) {}
}

class DtoWithTextModelId extends Data
{
    public function __construct(
        #[ModelId('uuid', 'text')]
        public FakeModel $model,
    ) {}
}

class DtoWithOptionalModelId extends Data
{
    public function __construct(
        #[ModelId('fakeModelId')]
        public FakeModel|Optional $model,
        public string $name,
    ) {}
}

class ActionWithPlainModel
{
    public function handle(DtoWithPlainModel $dto): void {}
}

class ActionWithModelId
{
    public function handle(DtoWithModelId $dto): void {}
}

class ActionWithTextModelId
{
    public function handle(DtoWithTextModelId $dto): void {}
}

class ActionWithOptionalModelId
{
    public function handle(DtoWithOptionalModelId $dto): void {}
}

// ---------------------------------------------------------------------------
// Tests
// ---------------------------------------------------------------------------

beforeEach(function () {
    $this->inspector = new DtoInspector(new FieldTypeMapper);
});

it('skips an eloquent model parameter without ModelId attribute', function () {
    $fields = $this->inspector->inspect(ActionWithPlainModel::class);

    $names = array_column($fields, 'name');
    expect($names)->not->toContain('model')
        ->and($names)->toContain('name');
});

it('includes the model parameter as a number field when annotated with ModelId', function () {
    $fields = $this->inspector->inspect(ActionWithModelId::class);

    $field = collect($fields)->firstWhere('name', 'fakeModelId');
    expect($field)->not->toBeNull()
        ->and($field['inputType'])->toBe('number')
        ->and($field['isNested'])->toBeFalse()
        ->and($field['isArray'])->toBeFalse()
        ->and($field['isFileField'])->toBeFalse();
});

it('marks the model field as required when annotated with Required', function () {
    $fields = $this->inspector->inspect(ActionWithModelId::class);

    $field = collect($fields)->firstWhere('name', 'fakeModelId');
    expect($field['required'])->toBeTrue();
});

it('uses a custom inputType from the ModelId attribute', function () {
    $fields = $this->inspector->inspect(ActionWithTextModelId::class);

    $field = collect($fields)->firstWhere('name', 'uuid');
    expect($field)->not->toBeNull()
        ->and($field['inputType'])->toBe('text');
});

it('marks the model field as not required when Optional', function () {
    $fields = $this->inspector->inspect(ActionWithOptionalModelId::class);

    $field = collect($fields)->firstWhere('name', 'fakeModelId');
    expect($field)->not->toBeNull()
        ->and($field['required'])->toBeFalse();
});
