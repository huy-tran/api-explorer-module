<?php

use Illuminate\Database\Eloquent\Model;
use Modules\ApiExplorer\Attributes\BodyField;
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

class DtoWithBodyField extends Data
{
    public function __construct(
        #[Required]
        #[BodyField('fakeModelId')]
        public FakeModel $model,
        public string $name,
    ) {}
}

class DtoWithTextBodyField extends Data
{
    public function __construct(
        #[BodyField('uuid', 'text')]
        public FakeModel $model,
    ) {}
}

class DtoWithOptionalBodyField extends Data
{
    public function __construct(
        #[BodyField('fakeModelId')]
        public FakeModel|Optional $model,
        public string $name,
    ) {}
}

class ActionWithPlainModel
{
    public function handle(DtoWithPlainModel $dto): void {}
}

class ActionWithBodyField
{
    public function handle(DtoWithBodyField $dto): void {}
}

class ActionWithTextBodyField
{
    public function handle(DtoWithTextBodyField $dto): void {}
}

class ActionWithOptionalBodyField
{
    public function handle(DtoWithOptionalBodyField $dto): void {}
}

// ---------------------------------------------------------------------------
// Tests
// ---------------------------------------------------------------------------

beforeEach(function () {
    $this->inspector = new DtoInspector(new FieldTypeMapper);
});

it('auto-derives the body field key from the model class name', function () {
    $fields = $this->inspector->inspect(ActionWithPlainModel::class);

    $field = collect($fields)->firstWhere('name', 'fakeModelId');
    expect($field)->not->toBeNull()
        ->and($field['inputType'])->toBe('number')
        ->and($field['isNested'])->toBeFalse()
        ->and($field['isArray'])->toBeFalse()
        ->and($field['isFileField'])->toBeFalse();
});

it('overrides the auto-derived key when BodyField attribute is present', function () {
    $fields = $this->inspector->inspect(ActionWithBodyField::class);

    $field = collect($fields)->firstWhere('name', 'fakeModelId');
    expect($field)->not->toBeNull()
        ->and($field['inputType'])->toBe('number');
});

it('marks the model field as required when annotated with Required', function () {
    $fields = $this->inspector->inspect(ActionWithBodyField::class);

    $field = collect($fields)->firstWhere('name', 'fakeModelId');
    expect($field['required'])->toBeTrue();
});

it('uses a custom inputType from the BodyField attribute', function () {
    $fields = $this->inspector->inspect(ActionWithTextBodyField::class);

    $field = collect($fields)->firstWhere('name', 'uuid');
    expect($field)->not->toBeNull()
        ->and($field['inputType'])->toBe('text');
});

it('marks the model field as not required when Optional', function () {
    $fields = $this->inspector->inspect(ActionWithOptionalBodyField::class);

    $field = collect($fields)->firstWhere('name', 'fakeModelId');
    expect($field)->not->toBeNull()
        ->and($field['required'])->toBeFalse();
});
