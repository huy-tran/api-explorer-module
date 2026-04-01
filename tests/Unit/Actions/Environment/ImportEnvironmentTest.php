<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\ApiExplorer\Actions\Environment\ImportEnvironment;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function (): void {
    Storage::fake('local');
    $this->action = app(ImportEnvironment::class);
});

it('imports a single md file entry', function (): void {
    $files = [
        ['name' => 'local', 'contents' => "baseUrl: https://app.test\n\nvars {\n  token: abc\n}"],
    ];

    $result = $this->action->handle($files);

    expect($result['imported'])->toBe(['local'])
        ->and($result['skipped'])->toBeEmpty();

    Storage::disk('local')->assertExists('api-explorer/environments/local.md');
});

it('imports multiple file entries', function (): void {
    $files = [
        ['name' => 'local', 'contents' => 'baseUrl: https://app.test'],
        ['name' => 'staging', 'contents' => 'baseUrl: https://staging.test'],
    ];

    $result = $this->action->handle($files);

    expect($result['imported'])->toBe(['local', 'staging'])
        ->and($result['skipped'])->toBeEmpty();
});

it('skips entries with invalid names', function (): void {
    $files = [
        ['name' => '../evil', 'contents' => 'baseUrl: https://app.test'],
        ['name' => 'valid', 'contents' => 'baseUrl: https://app.test'],
    ];

    $result = $this->action->handle($files);

    expect($result['imported'])->toBe(['valid'])
        ->and($result['skipped'])->toBe(['../evil']);

    Storage::disk('local')->assertMissing('api-explorer/environments/../evil.md');
    Storage::disk('local')->assertExists('api-explorer/environments/valid.md');
});

it('imports an md uploaded file via controller', function (): void {
    $contents = "baseUrl: https://app.test\n\nvars {\n  key: value\n}";
    $file = UploadedFile::fake()->createWithContent('myenv.md', $contents);

    $response = $this->postJson(route('api-explorer.environments.import'), ['file' => $file]);

    $response->assertOk()
        ->assertJsonPath('imported.0', 'myenv');

    Storage::disk('local')->assertExists('api-explorer/environments/myenv.md');
});

it('rejects a file with a disallowed extension', function (): void {
    $file = UploadedFile::fake()->create('config.json', 1);

    $response = $this->postJson(route('api-explorer.environments.import'), ['file' => $file]);

    $response->assertUnprocessable();
});
